<?php

declare(strict_types=1);

namespace Rector\Tests\Parallel\Command;

use Iterator;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Command\ProcessCommand;
use Rector\Core\Console\Command\WorkerCommand;
use Rector\Core\Kernel\RectorKernel;
use Rector\Parallel\Command\WorkerCommandLineFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class WorkerCommandLineFactoryTest extends AbstractKernelTestCase
{
    /**
     * @var string
     */
    private const COMMAND = 'command';

    /**
     * @var string
     */
    private const DUMMY_MAIN_SCRIPT = 'main_script';

    private WorkerCommandLineFactory $workerCommandLineFactory;

    private ProcessCommand $processCommand;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->workerCommandLineFactory = $this->getService(WorkerCommandLineFactory::class);
        $this->processCommand = $this->getService(ProcessCommand::class);
    }

    /**
     * @dataProvider provideData()
     * @param array<string, mixed> $inputParameters
     */
    public function test(array $inputParameters, string $expectedCommand): void
    {
        $inputDefinition = $this->prepareProcessCommandDefinition();
        $arrayInput = new ArrayInput($inputParameters, $inputDefinition);

        $workerCommandLine = $this->workerCommandLineFactory->create(
            self::DUMMY_MAIN_SCRIPT,
            CommandNaming::classToName(ProcessCommand::class),
            CommandNaming::classToName(WorkerCommand::class),
            null,
            $arrayInput,
            'identifier',
            2000
        );

        $this->assertSame($expectedCommand, $workerCommandLine);
    }

    /**
     * @return Iterator<array<int, array<string, string|string[]|bool>>|string[]>
     */
    public function provideData(): Iterator
    {
        $cliInputOptions = array_slice($_SERVER['argv'], 1);
        $cliInputOptionsAsString = implode("' '", $cliInputOptions);

        yield [
            [
                self::COMMAND => 'process',
                Option::SOURCE => ['src'],
            ],
            "'" . PHP_BINARY . "' '" . self::DUMMY_MAIN_SCRIPT . "' '" . $cliInputOptionsAsString . "' worker --port 2000 --identifier 'identifier' 'src' --output-format 'json' --no-ansi",
        ];

        yield [
            [
                self::COMMAND => 'process',
                Option::SOURCE => ['src'],
                '--' . Option::OUTPUT_FORMAT => ConsoleOutputFormatter::NAME,
            ],
            "'" . PHP_BINARY . "' '" . self::DUMMY_MAIN_SCRIPT . "' '" . $cliInputOptionsAsString . "' worker --port 2000 --identifier 'identifier' 'src' --output-format 'json' --no-ansi",
        ];
    }

    private function prepareProcessCommandDefinition(): InputDefinition
    {
        $inputDefinition = $this->processCommand->getDefinition();

        // not sure why, but the 1st argument "command" is missing; this is needed for a command name
        $arguments = $inputDefinition->getArguments();
        $commandInputArgument = new InputArgument(self::COMMAND, InputArgument::REQUIRED);
        $arguments = array_merge([$commandInputArgument], $arguments);

        $inputDefinition->setArguments($arguments);

        return $inputDefinition;
    }
}
