<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\Detector\ChangedFilesDetector;
final class UnchangedFilesFilter
{
    /**
     * @readonly
     */
    private ChangedFilesDetector $changedFilesDetector;
    public function __construct(ChangedFilesDetector $changedFilesDetector)
    {
        $this->changedFilesDetector = $changedFilesDetector;
    }
    /**
     * @param string[] $filePaths
     * @return string[]
     */
    public function filterFilePaths(array $filePaths) : array
    {
        $changedFileInfos = [];
        $filePaths = \array_unique($filePaths);
        foreach ($filePaths as $filePath) {
            if (!$this->changedFilesDetector->hasFileChanged($filePath)) {
                continue;
            }
            $changedFileInfos[] = $filePath;
            $this->changedFilesDetector->invalidateFile($filePath);
        }
        return $changedFileInfos;
    }
}
