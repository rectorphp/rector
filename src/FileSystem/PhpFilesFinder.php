<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpFilesFinder
{
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

        // filter out non-PHP php files, e.g. blade templates in Laravel
        $phpFileInfos = array_filter(
            $phpFileInfos,
            fn (SmartFileInfo $smartFileInfo): bool => ! \str_ends_with($smartFileInfo->getPathname(), '.blade.php')
        );

        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
