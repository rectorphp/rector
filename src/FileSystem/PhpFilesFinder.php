<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use RectorPrefix20210514\Nette\Utils\Strings;
use Rector\Caching\Application\CachedFileInfoFilterAndReporter;
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
     * @var \Rector\Caching\Application\CachedFileInfoFilterAndReporter
     */
    private $cachedFileInfoFilterAndReporter;
    public function __construct(\Rector\Core\FileSystem\FilesFinder $filesFinder, \Rector\Core\Configuration\Configuration $configuration, \Rector\Caching\Application\CachedFileInfoFilterAndReporter $cachedFileInfoFilterAndReporter)
    {
        $this->filesFinder = $filesFinder;
        $this->configuration = $configuration;
        $this->cachedFileInfoFilterAndReporter = $cachedFileInfoFilterAndReporter;
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
            return !\RectorPrefix20210514\Nette\Utils\Strings::endsWith($smartFileInfo->getPathname(), '.blade.php');
        });
        return $this->cachedFileInfoFilterAndReporter->filterFileInfos($phpFileInfos);
    }
}
