<?php

declare (strict_types=1);
namespace Rector\Core\Console\Style;

use RectorPrefix20220211\OndraM\CiDetector\CiDetector;
use RectorPrefix20220211\Symfony\Component\Console\Helper\ProgressBar;
use RectorPrefix20220211\Symfony\Component\Console\Style\SymfonyStyle;
final class RectorConsoleOutputStyle extends \RectorPrefix20220211\Symfony\Component\Console\Style\SymfonyStyle
{
    /**
     * @var bool|null
     */
    private $isCiDetected = null;
    /**
     * @see https://github.com/phpstan/phpstan-src/commit/0993d180e5a15a17631d525909356081be59ffeb
     */
    public function createProgressBar(int $max = 0) : \RectorPrefix20220211\Symfony\Component\Console\Helper\ProgressBar
    {
        $progressBar = parent::createProgressBar($max);
        $progressBar->setOverwrite(!$this->isCiDetected());
        $isCiDetected = $this->isCiDetected();
        $progressBar->setOverwrite(!$isCiDetected);
        if ($isCiDetected) {
            $progressBar->minSecondsBetweenRedraws(15);
            $progressBar->maxSecondsBetweenRedraws(30);
        } elseif (\DIRECTORY_SEPARATOR === '\\') {
            $progressBar->minSecondsBetweenRedraws(0.5);
            $progressBar->maxSecondsBetweenRedraws(2);
        } else {
            $progressBar->minSecondsBetweenRedraws(0.1);
            $progressBar->maxSecondsBetweenRedraws(0.5);
        }
        return $progressBar;
    }
    private function isCiDetected() : bool
    {
        if ($this->isCiDetected === null) {
            $ciDetector = new \RectorPrefix20220211\OndraM\CiDetector\CiDetector();
            $this->isCiDetected = $ciDetector->isCiDetected();
        }
        return $this->isCiDetected;
    }
}
