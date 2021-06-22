<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
use Rector\Core\Configuration\Configuration;
use Symplify\SmartFileSystem\SmartFileInfo;
final class PhpFilesFinder
{
    /**
     * @var \Rector\Core\FileSystem\FilesFinder
     */
    private $filesFinder;
    /**
     * @var \Rector\Core\Configuration\Configuration
     */
    private $configuration;
    /**
     * @var \Rector\Caching\UnchangedFilesFilter
     */
    private $unchangedFilesFilter;
    public function __construct(\Rector\Core\FileSystem\FilesFinder $filesFinder, \Rector\Core\Configuration\Configuration $configuration, \Rector\Caching\UnchangedFilesFilter $unchangedFilesFilter)
    {
        $this->filesFinder = $filesFinder;
        $this->configuration = $configuration;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
    }
    /**
     * @param string[] $paths
     * @return SmartFileInfo[]
     */
    public function findInPaths(array $paths) : array
    {
        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles($paths, $this->configuration->getFileExtensions());
        // filter out non-PHP php files, e.g. blade templates in Laravel
        $phpFileInfos = \array_filter($phpFileInfos, function (\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool {
            return \substr_compare($smartFileInfo->getPathname(), '.blade.php', -\strlen('.blade.php')) !== 0;
        });
        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
