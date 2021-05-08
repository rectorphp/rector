<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Application\ApplicationProgressBarInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\Application\File;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

final class ApplicationProgressBar implements ApplicationProgressBarInterface
{
    /**
     * Why 4? One for each cycle, so user sees some activity all the time:
     *
     * 1) parsing files
     * 2) main rectoring
     * 3) post-rectoring (removing files, importing names)
     * 4) printing
     *
     * @var int
     */
    private const PROGRESS_BAR_STEP_MULTIPLIER = 4;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        PrivatesAccessor $privatesAccessor,
        Configuration $configuration
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->privatesAccessor = $privatesAccessor;
        $this->configuration = $configuration;
    }

    public function start(int $count): void
    {
        if (! $this->shouldShowProgressBar()) {
            return;
        }

        $this->configureStepCount($count);
    }

    public function advance(File $file, string $phase): void
    {
        if ($this->symfonyStyle->isVerbose()) {
            $smartFileInfo = $file->getSmartFileInfo();
            $relativeFilePath = $smartFileInfo->getRelativeFilePathFromDirectory(getcwd());
            $message = sprintf('[%s] %s', $phase, $relativeFilePath);
            $this->symfonyStyle->writeln($message);
        } elseif ($this->configuration->shouldShowProgressBar()) {
            $this->symfonyStyle->progressAdvance();
        }
    }

    public function finish(): void
    {
        if (! $this->shouldShowProgressBar()) {
            return;
        }

        $this->symfonyStyle->progressFinish();
    }

    private function shouldShowProgressBar(): bool
    {
        if ($this->symfonyStyle->isVerbose()) {
            return false;
        }

        if (! $this->configuration->shouldShowProgressBar()) {
            return false;
        }

        return true;
    }

    /**
     * This prevent CI report flood with 1 file = 1 line in progress bar
     */
    private function configureStepCount(int $fileCount): void
    {
        $this->symfonyStyle->progressStart($fileCount * self::PROGRESS_BAR_STEP_MULTIPLIER);

        $progressBar = $this->privatesAccessor->getPrivateProperty($this->symfonyStyle, 'progressBar');
        if (! $progressBar instanceof \Symfony\Component\Console\Helper\ProgressBar) {
            throw new ShouldNotHappenException();
        }

        if ($progressBar->getMaxSteps() < 40) {
            return;
        }

        $redrawFrequency = (int) ($progressBar->getMaxSteps() / 20);
        $progressBar->setRedrawFrequency($redrawFrequency);
    }
}
