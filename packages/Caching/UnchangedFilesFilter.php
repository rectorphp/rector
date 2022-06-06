<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\Detector\ChangedFilesDetector;
use Symplify\SmartFileSystem\SmartFileInfo;
final class UnchangedFilesFilter
{
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    public function __construct(\Rector\Caching\Detector\ChangedFilesDetector $changedFilesDetector)
    {
        $this->changedFilesDetector = $changedFilesDetector;
    }
    /**
     * @param SmartFileInfo[]|string[] $fileInfos
     * @return SmartFileInfo[]
     */
    public function filterAndJoinWithDependentFileInfos(array $fileInfos) : array
    {
        $changedFileInfos = [];
        $dependentFileInfos = [];
        foreach ($fileInfos as $fileInfo) {
            if (\is_string($fileInfo)) {
                $fileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($fileInfo);
            }
            if (!$this->changedFilesDetector->hasFileChanged($fileInfo)) {
                continue;
            }
            $changedFileInfos[] = $fileInfo;
            $this->changedFilesDetector->invalidateFile($fileInfo);
            $dependentFileInfos = \array_merge($dependentFileInfos, $this->changedFilesDetector->getDependentFileInfos($fileInfo));
        }
        // add dependent files
        $dependentFileInfos = \array_merge($dependentFileInfos, $changedFileInfos);
        return \array_unique($dependentFileInfos);
    }
}
