<?php

declare(strict_types=1);

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

    public function __construct(
        private FilesFinder $filesFinder,
        private UnchangedFilesFilter $unchangedFilesFilter
    ) {
    }

    /**
     * @param string[] $paths
     * @return SmartFileInfo[]
     */
    public function findInPaths(array $paths): array
    {
        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles($paths);

        // filter out non-PHP files
        foreach ($phpFileInfos as $key => $phpFileInfo) {
            $pathName = $phpFileInfo->getPathname();
            foreach (self::NON_PHP_FILE_EXTENSIONS as $nonPHPFileExtension) {
                if (str_ends_with($pathName, $nonPHPFileExtension)) {
                    unset($phpFileInfos[$key]);
                    continue 2;
                }
            }
        }

        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
