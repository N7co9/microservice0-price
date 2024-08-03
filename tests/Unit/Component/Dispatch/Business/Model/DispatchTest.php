<?php
declare(strict_types=1);

namespace App\Tests\Unit\Component\Dispatch\Business\Model;

use App\Component\Archive\Business\ArchiveBusinessFacade;
use App\Component\Dispatch\Business\Model\Dispatch;
use App\Component\Import\Business\ImportBusinessFacade;
use App\Component\Import\Business\Model\Import;
use App\Component\Message\Message;
use App\Shared\DTO\ProductDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DispatchTest extends TestCase
{
    private MessageBusInterface $messageBusMock;
    private ImportBusinessFacade $import;
    private ArchiveBusinessFacade $archiveMock;
    private Dispatch $dispatch;

    protected function setUp(): void
    {
        $this->messageBusMock = $this->createMock(MessageBusInterface::class);

        $importModel = $this->createMock(Import::class);
        $this->import = new ImportBusinessFacade($importModel);

        $this->archiveMock = $this->createMock(ArchiveBusinessFacade::class);

        $_ENV['ARCHIVE_PATH'] = '/tmp/archive';

        $this->dispatch = new Dispatch(
            $this->messageBusMock,
            $this->import,
            $this->archiveMock
        );
    }

    public function testDispatch(): void
    {
        $fileLocations = ['/path/to/file1.xml', '/path/to/file2.xml'];
        $productDTOs = [
            new ProductDTO('product1', [], []),
            new ProductDTO('product2', [], [])
        ];

        $importReflection = new \ReflectionClass(ImportBusinessFacade::class);
        $importProperty = $importReflection->getProperty('import');
        $importModelMock = $importProperty->getValue($this->import);
        $importModelMock->expects($this->exactly(2))
            ->method('parse')
            ->willReturnOnConsecutiveCalls($productDTOs, []);

        $this->messageBusMock->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function ($message, $stamps) {
                $this->assertInstanceOf(Message::class, $message);
                $this->assertInstanceOf(ProductDTO::class, $message->getContent());
                $this->assertCount(1, $stamps);
                $this->assertInstanceOf(AmqpStamp::class, $stamps[0]);
                $this->assertEquals(Dispatch::routingKey, $stamps[0]->getRoutingKey());
                return new Envelope($message);
            });

        $this->archiveMock->expects($this->exactly(2))
            ->method('archiveProcessedData')
            ->willReturnCallback(function ($source, $destination) use ($fileLocations) {
                $this->assertContains($source, $fileLocations);
                $this->assertStringStartsWith('/tmp/archive/', $destination);
                return true;
            });

        $this->dispatch->dispatch($fileLocations);
    }

    public function testDispatchWithEmptyFileLocations(): void
    {
        $importReflection = new \ReflectionClass(ImportBusinessFacade::class);
        $importProperty = $importReflection->getProperty('import');
        $importModelMock = $importProperty->getValue($this->import);
        $importModelMock->expects($this->never())->method('parse');

        $this->messageBusMock->expects($this->never())->method('dispatch');
        $this->archiveMock->expects($this->never())->method('archiveProcessedData');

        $this->dispatch->dispatch([]);
    }

    public function testDispatchWithEmptyImportResult(): void
    {
        $fileLocations = ['/path/to/empty_file.xml'];

        $importReflection = new \ReflectionClass(ImportBusinessFacade::class);
        $importProperty = $importReflection->getProperty('import');
        $importModelMock = $importProperty->getValue($this->import);
        $importModelMock->expects($this->once())
            ->method('parse')
            ->willReturn([]);

        $this->messageBusMock->expects($this->never())->method('dispatch');

        $this->archiveMock->expects($this->once())
            ->method('archiveProcessedData')
            ->with(
                $fileLocations[0],
                $this->stringStartsWith('/tmp/archive/')
            )
            ->willReturn(true);

        $this->dispatch->dispatch($fileLocations);
    }
}