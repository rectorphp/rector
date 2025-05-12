<?php

declare (strict_types=1);
namespace Rector\Console\Style;

use Rector\Util\Reflection\PrivatesAccessor;
use RectorPrefix202505\Symfony\Component\Console\Application;
use RectorPrefix202505\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202505\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202505\Symfony\Component\Console\Output\OutputInterface;
final class SymfonyStyleFactory
{
    /**
     * @readonly
     */
    private PrivatesAccessor $privatesAccessor;
    public function __construct(PrivatesAccessor $privatesAccessor)
    {
        $this->privatesAccessor = $privatesAccessor;
    }
    /**
     * @api
     */
    public function create() : \Rector\Console\Style\RectorStyle
    {
        // to prevent missing argv indexes
        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = [];
        }
        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();
        // to configure all -v, -vv, -vvv options without memory-lock to Application run() arguments
        $this->privatesAccessor->callPrivateMethod(new Application(), 'configureIO', [$argvInput, $consoleOutput]);
        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }
        // disable output for tests
        if ($this->isPHPUnitRun()) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        return new \Rector\Console\Style\RectorStyle($argvInput, $consoleOutput);
    }
    /**
     * Never ever used static methods if not necessary, this is just handy for tests + src to prevent duplication.
     */
    private function isPHPUnitRun() : bool
    {
        return \defined('PHPUNIT_COMPOSER_INSTALL') || \defined('__PHPUNIT_PHAR__');
    }
}
