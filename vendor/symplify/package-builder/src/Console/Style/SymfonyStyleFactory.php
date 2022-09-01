<?php

declare (strict_types=1);
namespace RectorPrefix202209\Symplify\PackageBuilder\Console\Style;

use RectorPrefix202209\Symfony\Component\Console\Application;
use RectorPrefix202209\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix202209\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix202209\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202209\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202209\Symplify\PackageBuilder\Reflection\PrivatesCaller;
/**
 * @api
 */
final class SymfonyStyleFactory
{
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesCaller
     */
    private $privatesCaller;
    public function __construct()
    {
        $this->privatesCaller = new PrivatesCaller();
    }
    public function create() : SymfonyStyle
    {
        // to prevent missing argv indexes
        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = [];
        }
        $argvInput = new ArgvInput();
        $consoleOutput = new ConsoleOutput();
        // to configure all -v, -vv, -vvv options without memory-lock to Application run() arguments
        $this->privatesCaller->callPrivateMethod(new Application(), 'configureIO', [$argvInput, $consoleOutput]);
        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        }
        // disable output for tests
        if ($this->isPHPUnitRun()) {
            $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        return new SymfonyStyle($argvInput, $consoleOutput);
    }
    /**
     * Never ever used static methods if not neccesary, this is just handy for tests + src to prevent duplication.
     */
    private function isPHPUnitRun() : bool
    {
        return \defined('RectorPrefix202209\\PHPUNIT_COMPOSER_INSTALL') || \defined('RectorPrefix202209\\__PHPUNIT_PHAR__');
    }
}
