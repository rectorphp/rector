<?php

declare(strict_types=1);

namespace Rector\Caching\Application;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\UnchangedFilesFilter;
use Rector\Core\Configuration\Configuration;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CachedFileInfoFilterAndReporter
{
    public function __construct(
        private Configuration $configuration,
        private ChangedFilesDetector $changedFilesDetector,
        private UnchangedFilesFilter $unchangedFilesFilter
    ) {
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     * @return SmartFileInfo[]
     */
    public function filterFileInfos(array $phpFileInfos): array
    {
        // cache stuff
        if ($this->configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
            return $phpFileInfos;
        }

        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
