<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\StaticReflection;

use RectorPrefix20220606\Rector\Core\FileSystem\PhpFilesFinder;
use RectorPrefix20220606\Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use RectorPrefix20220606\Symplify\SmartFileSystem\FileSystemFilter;
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
    public function __construct(FileSystemFilter $fileSystemFilter, DynamicSourceLocatorProvider $dynamicSourceLocatorProvider, PhpFilesFinder $phpFilesFinder)
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
