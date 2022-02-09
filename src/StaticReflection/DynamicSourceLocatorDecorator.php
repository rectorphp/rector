<?php

declare (strict_types=1);
namespace Rector\Core\StaticReflection;

use Rector\Core\FileSystem\PhpFilesFinder;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use RectorPrefix20220209\Symplify\SmartFileSystem\FileSystemFilter;
/**
 * @see https://phpstan.org/blog/zero-config-analysis-with-static-reflection
 * @see https://github.com/rectorphp/rector/issues/3490
 */
final class DynamicSourceLocatorDecorator
{
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\FileSystemFilter
     */
    private $fileSystemFilter;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider
     */
    private $dynamicSourceLocatorProvider;
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\PhpFilesFinder
     */
    private $phpFilesFinder;
    public function __construct(\RectorPrefix20220209\Symplify\SmartFileSystem\FileSystemFilter $fileSystemFilter, \Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider $dynamicSourceLocatorProvider, \Rector\Core\FileSystem\PhpFilesFinder $phpFilesFinder)
    {
        $this->fileSystemFilter = $fileSystemFilter;
        $this->dynamicSourceLocatorProvider = $dynamicSourceLocatorProvider;
        $this->phpFilesFinder = $phpFilesFinder;
    }
    /**
     * @param string[] $paths
     */
    public function addPaths(array $paths) : void
    {
        if ($paths === []) {
            return;
        }
        $files = $this->fileSystemFilter->filterFiles($paths);
        $this->dynamicSourceLocatorProvider->addFiles($files);
        $directories = $this->fileSystemFilter->filterDirectories($paths);
        foreach ($directories as $directory) {
            $filesInfosInDirectory = $this->phpFilesFinder->findInPaths([$directory]);
            $filesInDirectory = [];
            foreach ($filesInfosInDirectory as $fileInfoInDirectory) {
                $filesInDirectory[] = $fileInfoInDirectory->getRealPath();
            }
            $this->dynamicSourceLocatorProvider->addFilesByDirectory($directory, $filesInDirectory);
        }
    }
}
