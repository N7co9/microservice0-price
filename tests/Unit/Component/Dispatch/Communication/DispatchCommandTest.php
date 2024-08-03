<?php
declare(strict_types=1);

namespace App\Tests\Unit\Component\Dispatch\Communication;

use App\Component\Dispatch\Business\Model\Dispatch;
use App\Component\Dispatch\Communication\DispatchCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class DispatchCommandTest extends TestCase
{
    private Dispatch $dispatchMock;
    private DispatchCommand $command;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->dispatchMock = $this->createMock(Dispatch::class);
        $this->command = new DispatchCommand($this->dispatchMock);

        $application = new Application();
        $application->add($this->command);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithFileLocationsAsArguments(): void
    {
        $fileLocations = ['/path/to/file1.xml', '/path/to/file2.xml'];

        $this->dispatchMock->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($fileLocations));

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'fileLocations' => $fileLocations,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Dispatching Upstream Messages', $output);
        $this->assertStringContainsString('Files processed and archived successfully.', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithUserInput(): void
    {
        $fileLocations = ['/path/to/file1.xml', '/path/to/file2.xml'];
        $input = implode(' ', $fileLocations);

        $questionHelper = $this->createMock(QuestionHelper::class);
        $questionHelper->expects($this->once())
            ->method('ask')
            ->willReturn($input);

        $this->command->getHelperSet()->set($questionHelper, 'question');

        $this->dispatchMock->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($fileLocations));

        $this->commandTester->execute([
            'command' => $this->command->getName(),
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Dispatching Upstream Messages', $output);
        $this->assertStringContainsString('Files processed and archived successfully.', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }


    public function testExecuteWithDispatchException(): void
    {
        $fileLocations = ['/path/to/file1.xml'];

        $this->dispatchMock->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($fileLocations))
            ->willThrowException(new \Exception('Dispatch failed'));

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'fileLocations' => $fileLocations,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Dispatching Upstream Messages', $output);
        $this->assertStringContainsString('Error: Dispatch failed', $output);
        $this->assertEquals(1, $this->commandTester->getStatusCode());
    }

    public function testCommandConfiguration(): void
    {
        $this->assertEquals('app:dispatch', $this->command->getName());
        $this->assertEquals(['app:send'], $this->command->getAliases());
        $this->assertEquals('Sends Stock Update Messages as ProductDTOs.', $this->command->getDescription());
        $this->assertFalse($this->command->isHidden());

        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('fileLocations'));
        $this->assertTrue($definition->getArgument('fileLocations')->isArray());
    }

    public function testExecuteWithMultipleFileLocationsAndSpaces(): void
    {
        $fileLocations = ['/path/to/file with spaces.xml', '/path/to/another file.xml'];

        $this->dispatchMock->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($fileLocations));

        $this->commandTester->execute([
            'command' => $this->command->getName(),
            'fileLocations' => $fileLocations,
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Dispatching Upstream Messages', $output);
        $this->assertStringContainsString('Files processed and archived successfully.', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}