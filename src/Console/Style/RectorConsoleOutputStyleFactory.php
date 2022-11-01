<?php

declare (strict_types=1);
namespace Rector\Core\Console\Style;

use Rector\Core\Util\Reflection\PrivatesAccessor;
use RectorPrefix202211\Symfony\Component\Console\Application;
use RectorPrefix202211\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202211\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202211\Symfony\Component\Console\Output\OutputInterface;
final class RectorConsoleOutputStyleFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Util\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    public function __construct(PrivatesAccessor $privatesAccessor)
    {
        $this->privatesAccessor = $privatesAccessor;
    }
    public function create() : \Rector\Core\Console\Style\RectorConsoleOutputStyle
    {
        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();
        // to configure all -v, -vv, -vvv options without memory-lock to Application run() arguments
        $this->privatesAccessor->callPrivateMethod(new Application(), 'configureIO', [$argvInput, $consoleOutput]);
        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }
        return new \Rector\Core\Console\Style\RectorConsoleOutputStyle($argvInput, $consoleOutput);
    }
}
