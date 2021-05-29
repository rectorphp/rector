<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Rector\Caching\Application\CachedFileInfoFilterAndReporter;
use Rector\Core\Configuration\Configuration;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpFilesFinder
{
    public function __construct(
        private FilesFinder $filesFinder,
        private Configuration $configuration,
        private CachedFileInfoFilterAndReporter $cachedFileInfoFilterAndReporter
    ) {
    }

    /**
     * @param string[] $paths
     * @return SmartFileInfo[]
     */
    public function findInPaths(array $paths): array
    {
        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles(
            $paths,
            $this->configuration->getFileExtensions()
        );

        // filter out non-PHP php files, e.g. blade templates in Laravel
        $phpFileInfos = array_filter(
            $phpFileInfos,
            fn (SmartFileInfo $smartFileInfo): bool => ! \str_ends_with($smartFileInfo->getPathname(), '.blade.php')
        );

        return $this->cachedFileInfoFilterAndReporter->filterFileInfos($phpFileInfos);
    }
}
