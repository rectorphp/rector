<?php

declare (strict_types=1);
namespace Rector\Core\Console\Style;

use RectorPrefix20220531\OndraM\CiDetector\CiDetector;
use RectorPrefix20220531\Symfony\Component\Console\Exception\RuntimeException;
use RectorPrefix20220531\Symfony\Component\Console\Helper\ProgressBar;
use RectorPrefix20220531\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220531\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220531\Symfony\Component\Console\Style\SymfonyStyle;
final class RectorConsoleOutputStyle extends \RectorPrefix20220531\Symfony\Component\Console\Style\SymfonyStyle
{
    /**
     * @var mixed|ProgressBar
     */
    public $progressBar;
    /**
     * @var bool|null
     */
    private $isCiDetected = null;
    public function __construct(\RectorPrefix20220531\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220531\Symfony\Component\Console\Output\OutputInterface $output)
    {
        parent::__construct($input, $output);
        // silent output in tests
        if (\defined('PHPUNIT_COMPOSER_INSTALL')) {
            $this->setVerbosity(\RectorPrefix20220531\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET);
        }
    }
    /**
     * @see https://github.com/phpstan/phpstan-src/commit/0993d180e5a15a17631d525909356081be59ffeb
     */
    public function createProgressBar(int $max = 0) : \RectorPrefix20220531\Symfony\Component\Console\Helper\ProgressBar
    {
        $progressBar = parent::createProgressBar($max);
        $progressBar->setOverwrite(!$this->isCiDetected());
        $isCiDetected = $this->isCiDetected();
        $progressBar->setOverwrite(!$isCiDetected);
        if ($isCiDetected) {
            $progressBar->minSecondsBetweenRedraws(15);
            $progressBar->maxSecondsBetweenRedraws(30);
        } elseif (\DIRECTORY_SEPARATOR === '\\') {
            // windows
            $progressBar->minSecondsBetweenRedraws(0.5);
            $progressBar->maxSecondsBetweenRedraws(2);
        } else {
            // *nix
            $progressBar->minSecondsBetweenRedraws(0.1);
            $progressBar->maxSecondsBetweenRedraws(0.5);
        }
        $this->progressBar = $progressBar;
        return $progressBar;
    }
    public function progressAdvance(int $step = 1) : void
    {
        // hide progress bar in tests
        if (\defined('PHPUNIT_COMPOSER_INSTALL')) {
            return;
        }
        $progressBar = $this->getProgressBar();
        $progressBar->advance($step);
    }
    private function isCiDetected() : bool
    {
        if ($this->isCiDetected === null) {
            $ciDetector = new \RectorPrefix20220531\OndraM\CiDetector\CiDetector();
            $this->isCiDetected = $ciDetector->isCiDetected();
        }
        return $this->isCiDetected;
    }
    private function getProgressBar() : \RectorPrefix20220531\Symfony\Component\Console\Helper\ProgressBar
    {
        if (!isset($this->progressBar)) {
            throw new \RectorPrefix20220531\Symfony\Component\Console\Exception\RuntimeException('The ProgressBar is not started.');
        }
        return $this->progressBar;
    }
}
