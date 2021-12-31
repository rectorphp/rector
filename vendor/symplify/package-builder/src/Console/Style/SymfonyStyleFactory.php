<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\PackageBuilder\Console\Style;

use RectorPrefix20211231\Symfony\Component\Console\Application;
use RectorPrefix20211231\Symfony\Component\Console\Input\ArgvInput;
use RectorPrefix20211231\Symfony\Component\Console\Output\ConsoleOutput;
use RectorPrefix20211231\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20211231\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20211231\Symplify\EasyTesting\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix20211231\Symplify\PackageBuilder\Reflection\PrivatesCaller;
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
        $this->privatesCaller = new \RectorPrefix20211231\Symplify\PackageBuilder\Reflection\PrivatesCaller();
    }
    public function create() : \RectorPrefix20211231\Symfony\Component\Console\Style\SymfonyStyle
    {
        // to prevent missing argv indexes
        if (!isset($_SERVER['argv'])) {
            $_SERVER['argv'] = [];
        }
        $argvInput = new \RectorPrefix20211231\Symfony\Component\Console\Input\ArgvInput();
        $consoleOutput = new \RectorPrefix20211231\Symfony\Component\Console\Output\ConsoleOutput();
        // to configure all -v, -vv, -vvv options without memory-lock to Application run() arguments
        $this->privatesCaller->callPrivateMethod(new \RectorPrefix20211231\Symfony\Component\Console\Application(), 'configureIO', [$argvInput, $consoleOutput]);
        // --debug is called
        if ($argvInput->hasParameterOption('--debug')) {
            $consoleOutput->setVerbosity(\RectorPrefix20211231\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_DEBUG);
        }
        // disable output for tests
        if (\RectorPrefix20211231\Symplify\EasyTesting\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $consoleOutput->setVerbosity(\RectorPrefix20211231\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET);
        }
        return new \RectorPrefix20211231\Symfony\Component\Console\Style\SymfonyStyle($argvInput, $consoleOutput);
    }
}
