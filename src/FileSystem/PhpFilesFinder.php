<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
use Symplify\SmartFileSystem\SmartFileInfo;
final class PhpFilesFinder
{
    /**
     * @var \Rector\Core\FileSystem\FilesFinder
     */
    private $filesFinder;
    /**
     * @var \Rector\Caching\UnchangedFilesFilter
     */
    private $unchangedFilesFilter;
    public function __construct(\Rector\Core\FileSystem\FilesFinder $filesFinder, \Rector\Caching\UnchangedFilesFilter $unchangedFilesFilter)
    {
        $this->filesFinder = $filesFinder;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
    }
    /**
     * @param string[] $paths
     * @return SmartFileInfo[]
     */
    public function findInPaths(array $paths) : array
    {
        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles($paths);
        // filter out non-PHP php files, e.g. blade templates in Laravel
        $phpFileInfos = \array_filter($phpFileInfos, function (\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool {
            return \substr_compare($smartFileInfo->getPathname(), '.blade.php', -\strlen('.blade.php')) !== 0;
        });
        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
