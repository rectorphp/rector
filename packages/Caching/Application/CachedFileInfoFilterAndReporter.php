<?php

declare(strict_types=1);

namespace Rector\Caching\Application;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\UnchangedFilesFilter;
use Rector\Core\Configuration\Configuration;
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
     * @var UnchangedFilesFilter
     */
    private $unchangedFilesFilter;

    public function __construct(
        Configuration $configuration,
        ChangedFilesDetector $changedFilesDetector,
        UnchangedFilesFilter $unchangedFilesFilter
    ) {
        $this->configuration = $configuration;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     * @return SmartFileInfo[]
     */
    public function filterFileInfos(array $phpFileInfos): array
    {
        if (! $this->configuration->isCacheEnabled() || $this->configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
            return $phpFileInfos;
        }

        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
