<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use RectorPrefix20220513\Symplify\SmartFileSystem\FileSystemGuard;
final class FilesystemTweaker
{
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    private $fileSystemGuard;
    public function __construct(\RectorPrefix20220513\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard)
    {
        $this->fileSystemGuard = $fileSystemGuard;
    }
    /**
     * This will turn paths like "src/Symfony/Component/*\/Tests" to existing directory paths
     *
     * @param string[] $directories
     * @return string[]
     */
    public function resolveDirectoriesWithFnmatch(array $directories) : array
    {
        $absoluteDirectories = [];
        foreach ($directories as $directory) {
            // is fnmatch for directories
            if (\strpos($directory, '*') !== \false) {
                $foundDirectories = $this->findDirectoriesInGlob($directory);
                $absoluteDirectories = \array_merge($absoluteDirectories, $foundDirectories);
            } else {
                // is classic directory
                $this->fileSystemGuard->ensureDirectoryExists($directory, '');
                $absoluteDirectories[] = $directory;
            }
        }
        return $absoluteDirectories;
    }
    /**
     * This will turn paths like "src/Symfony/Component/*\/Tests" to existing directory paths
     *
     * @param string[] $paths
     *
     * @return string[]
     */
    public function resolveWithFnmatch(array $paths) : array
    {
        $absolutePathsFound = [];
        foreach ($paths as $path) {
            if (\strpos($path, '*') !== \false) {
                $foundPaths = $this->foundInGlob($path);
                $absolutePathsFound = \array_merge($absolutePathsFound, $foundPaths);
            } else {
                $absolutePathsFound[] = $path;
            }
        }
        return $absolutePathsFound;
    }
    /**
     * @return string[]
     */
    private function findDirectoriesInGlob(string $directory) : array
    {
        /** @var string[] $foundDirectories */
        $foundDirectories = (array) \glob($directory, \GLOB_ONLYDIR);
        return $foundDirectories;
    }
    /**
     * @return string[]
     */
    private function foundInGlob(string $path) : array
    {
        /** @var string[] $paths */
        $paths = (array) \glob($path);
        return \array_filter($paths, function (string $path) : bool {
            return \file_exists($path);
        });
    }
}
