<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
final class PhpFilesFinder
{
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilesFinder
     */
    private $filesFinder;
    /**
     * @readonly
     * @var \Rector\Caching\UnchangedFilesFilter
     */
    private $unchangedFilesFilter;
    public function __construct(\Rector\Core\FileSystem\FilesFinder $filesFinder, UnchangedFilesFilter $unchangedFilesFilter)
    {
        $this->filesFinder = $filesFinder;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
    }
    /**
     * @param string[] $paths
     * @return string[]
     */
    public function findInPaths(array $paths) : array
    {
        $filePaths = $this->filesFinder->findInDirectoriesAndFiles($paths, ['php'], \false);
        // filter out non-PHP files
        foreach ($filePaths as $key => $filePath) {
            /**
             *  check .blade.php early so next .php check in next if can be skipped
             */
            if (\substr_compare($filePath, '.blade.php', -\strlen('.blade.php')) === 0) {
                unset($filePaths[$key]);
            }
        }
        return $this->unchangedFilesFilter->filterFileInfos($filePaths);
    }
}
