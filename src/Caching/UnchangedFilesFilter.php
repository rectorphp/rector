<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
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
    public function filterFilePaths(array $filePaths): array
    {
        $changedFileInfos = [];
        $filePaths = array_unique($filePaths);
        foreach ($filePaths as $filePath) {
            if (!$this->changedFilesDetector->hasFileChanged($filePath)) {
                continue;
            }
            $changedFileInfos[] = $filePath;
            $this->changedFilesDetector->invalidateFile($filePath);
        }
        // some files were served from cache, so rules ran on a subset only - unused skip reporting
        // would then flag every cached file's skip as falsely unused
        if (count($changedFileInfos) < count($filePaths)) {
            SimpleParameterProvider::setParameter(Option::IS_CACHED_RUN, \true);
        }
        return $changedFileInfos;
    }
}
