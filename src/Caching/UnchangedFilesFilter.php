<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\Detector\ChangedFilesDetector;
final class UnchangedFilesFilter
{
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    public function __construct(ChangedFilesDetector $changedFilesDetector)
    {
        $this->changedFilesDetector = $changedFilesDetector;
    }
    /**
     * @param string[] $filePaths
     * @return string[]
     */
    public function filterFileInfos(array $filePaths) : array
    {
        $changedFileInfos = [];
        foreach ($filePaths as $filePath) {
            if (!$this->changedFilesDetector->hasFileChanged($filePath)) {
                continue;
            }
            $changedFileInfos[] = $filePath;
            $this->changedFilesDetector->invalidateFile($filePath);
        }
        return \array_unique($changedFileInfos);
    }
}
