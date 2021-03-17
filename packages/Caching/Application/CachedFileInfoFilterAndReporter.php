<?php

declare(strict_types=1);

namespace Rector\Caching\Application;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\UnchangedFilesFilter;
use Rector\Core\Configuration\Configuration;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CachedFileInfoFilterAndReporter
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ChangedFilesDetector
     */
    private $changedFilesDetector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var UnchangedFilesFilter
     */
    private $unchangedFilesFilter;

    public function __construct(
        Configuration $configuration,
        ChangedFilesDetector $changedFilesDetector,
        SymfonyStyle $symfonyStyle,
        UnchangedFilesFilter $unchangedFilesFilter
    ) {
        $this->configuration = $configuration;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->symfonyStyle = $symfonyStyle;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     * @return SmartFileInfo[]
     */
    public function filterFileInfos(array $phpFileInfos): array
    {
        if (! $this->configuration->isCacheEnabled()) {
            return $phpFileInfos;
        }

        // cache stuff
        if ($this->configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
        }

        if ($this->configuration->isCacheDebug()) {
            $message = sprintf('[cache] %d files before cache filter', count($phpFileInfos));
            $this->symfonyStyle->note($message);
        }

        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
