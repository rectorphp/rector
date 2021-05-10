<?php

declare (strict_types=1);
namespace Rector\Caching\Application;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\UnchangedFilesFilter;
use Rector\Core\Configuration\Configuration;
use Symplify\SmartFileSystem\SmartFileInfo;
final class CachedFileInfoFilterAndReporter
{
    /**
     * @var \Rector\Core\Configuration\Configuration
     */
    private $configuration;
    /**
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @var \Rector\Caching\UnchangedFilesFilter
     */
    private $unchangedFilesFilter;
    public function __construct(\Rector\Core\Configuration\Configuration $configuration, \Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector, \Rector\Caching\UnchangedFilesFilter $unchangedFilesFilter)
    {
        $this->configuration = $configuration;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
    }
    /**
     * @param SmartFileInfo[] $phpFileInfos
     * @return SmartFileInfo[]
     */
    public function filterFileInfos(array $phpFileInfos) : array
    {
        if (!$this->configuration->isCacheEnabled()) {
            return $phpFileInfos;
        }
        // cache stuff
        if ($this->configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
        }
        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
