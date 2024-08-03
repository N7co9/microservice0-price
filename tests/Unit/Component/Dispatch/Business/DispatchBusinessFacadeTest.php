<?php
declare(strict_types=1);

namespace App\Tests\Unit\Component\Dispatch\Business;

use App\Component\Dispatch\Business\DispatchBusinessFacade;
use App\Component\Dispatch\Business\Model\Dispatch;
use PHPUnit\Framework\TestCase;

class DispatchBusinessFacadeTest extends TestCase
{
    private Dispatch $dispatchMock;
    private DispatchBusinessFacade $dispatchBusinessFacade;

    protected function setUp(): void
    {
        $this->dispatchMock = $this->createMock(Dispatch::class);
        $this->dispatchBusinessFacade = new DispatchBusinessFacade($this->dispatchMock);
    }

    public function testDispatch(): void
    {
        $fileLocations = ['/path/to/file1.xml', '/path/to/file2.xml'];

        $this->dispatchMock->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($fileLocations));

        $this->dispatchBusinessFacade->dispatch($fileLocations);
    }

    public function testDispatchWithEmptyArray(): void
    {
        $fileLocations = [];

        $this->dispatchMock->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($fileLocations));

        $this->dispatchBusinessFacade->dispatch($fileLocations);
    }
}