<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Nette\Utils\Strings;
use Rector\Caching\Application\CachedFileInfoFilterAndReporter;
use Rector\Core\Configuration\Configuration;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpFilesFinder
{
    /**
     * @var FilesFinder
     */
    private $filesFinder;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var CachedFileInfoFilterAndReporter
     */
    private $cachedFileInfoFilterAndReporter;

    public function __construct(
        FilesFinder $filesFinder,
        Configuration $configuration,
        CachedFileInfoFilterAndReporter $cachedFileInfoFilterAndReporter
    ) {
        $this->filesFinder = $filesFinder;
        $this->configuration = $configuration;
        $this->cachedFileInfoFilterAndReporter = $cachedFileInfoFilterAndReporter;
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
        $phpFileInfos = array_filter($phpFileInfos, function (SmartFileInfo $smartFileInfo): bool {
            return ! Strings::endsWith($smartFileInfo->getPathname(), '.blade.php');
        });

        return $this->cachedFileInfoFilterAndReporter->filterFileInfos($phpFileInfos);
    }
}
