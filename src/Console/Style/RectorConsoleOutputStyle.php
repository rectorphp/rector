<?php

declare(strict_types=1);

namespace Rector\Core\Console\Style;

use OndraM\CiDetector\CiDetector;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RectorConsoleOutputStyle extends SymfonyStyle
{
    private bool|null $isCiDetected = null;

    /**
     * @see https://github.com/phpstan/phpstan-src/commit/0993d180e5a15a17631d525909356081be59ffeb
     */
    public function createProgressBar(int $max = 0): ProgressBar
    {
        $progressBar = parent::createProgressBar($max);
        $progressBar->setOverwrite(! $this->isCiDetected());

        $isCiDetected = $this->isCiDetected();
        $progressBar->setOverwrite(! $isCiDetected);

        if ($isCiDetected) {
            $progressBar->minSecondsBetweenRedraws(15);
            $progressBar->maxSecondsBetweenRedraws(30);
        } elseif (DIRECTORY_SEPARATOR === '\\') {
            $progressBar->minSecondsBetweenRedraws(0.5);
            $progressBar->maxSecondsBetweenRedraws(2);
        } else {
            $progressBar->minSecondsBetweenRedraws(0.1);
            $progressBar->maxSecondsBetweenRedraws(0.5);
        }

        return $progressBar;
    }

    private function isCiDetected(): bool
    {
        if ($this->isCiDetected === null) {
            $ciDetector = new CiDetector();
            $this->isCiDetected = $ciDetector->isCiDetected();
        }

        return $this->isCiDetected;
    }
}
