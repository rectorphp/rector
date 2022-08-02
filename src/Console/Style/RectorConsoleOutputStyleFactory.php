<?php

declare (strict_types=1);
namespace Rector\Core\Console\Style;

use RectorPrefix202208\Symfony\Component\Console\Application;
use RectorPrefix202208\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202208\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202208\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202208\Symplify\PackageBuilder\Reflection\PrivatesCaller;
final class RectorConsoleOutputStyleFactory
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\PrivatesCaller
     */
    private $privatesCaller;
    public function __construct(PrivatesCaller $privatesCaller)
    {
        $this->privatesCaller = $privatesCaller;
    }
    public function create() : \Rector\Core\Console\Style\RectorConsoleOutputStyle
    {
        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();
        // to configure all -v, -vv, -vvv options without memory-lock to Application run() arguments
        $this->privatesCaller->callPrivateMethod(new Application(), 'configureIO', [$argvInput, $consoleOutput]);
        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }
        return new \Rector\Core\Console\Style\RectorConsoleOutputStyle($argvInput, $consoleOutput);
    }
}
