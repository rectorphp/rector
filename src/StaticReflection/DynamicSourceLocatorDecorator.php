<?php

declare(strict_types=1);

namespace Rector\Core\StaticReflection;

use Rector\Core\FileSystem\PhpFilesFinder;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Symplify\SmartFileSystem\FileSystemFilter;

/**
 * @see https://phpstan.org/blog/zero-config-analysis-with-static-reflection
 * @see https://github.com/rectorphp/rector/issues/3490
 */
final class DynamicSourceLocatorDecorator
{
    public function __construct(
        private FileSystemFilter $fileSystemFilter,
        private DynamicSourceLocatorProvider $dynamicSourceLocatorProvider,
        private PhpFilesFinder $phpFilesFinder
    ) {
    }

    /**
     * @param string[] $paths
     */
    public function addPaths(array $paths): void
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
