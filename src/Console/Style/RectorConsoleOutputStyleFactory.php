<?php

declare(strict_types=1);

namespace Rector\Core\Console\Style;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class RectorConsoleOutputStyleFactory
{
    public function __construct(
        private readonly PrivatesCaller $privatesCaller
    ) {
    }

    public function create(): RectorConsoleOutputStyle
    {
        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();

        // to configure all -v, -vv, -vvv options without memory-lock to Application run() arguments
        $this->privatesCaller->callPrivateMethod(new Application(), 'configureIO', [$argvInput, $consoleOutput]);
        $debugArgvInputParameterOption = $argvInput->getParameterOption('--debug');

        // --debug is called
        if ($debugArgvInputParameterOption === null) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }

        return new RectorConsoleOutputStyle($argvInput, $consoleOutput);
    }
}
