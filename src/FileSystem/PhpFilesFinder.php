<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
use Symplify\SmartFileSystem\SmartFileInfo;
final class PhpFilesFinder
{
    /**
     * @var string[]
     */
    private const NON_PHP_FILE_EXTENSIONS = [
        // Laravel
        '.blade.php',
        // Smarty
        '.tpl',
    ];
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
        // filter out non-PHP files
        foreach ($phpFileInfos as $key => $phpFileInfo) {
            $pathName = $phpFileInfo->getPathname();
            foreach (self::NON_PHP_FILE_EXTENSIONS as $nonPHPFileExtension) {
                if (\substr_compare($pathName, $nonPHPFileExtension, -\strlen($nonPHPFileExtension)) === 0) {
                    unset($phpFileInfos[$key]);
                    continue 2;
                }
            }
        }
        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
