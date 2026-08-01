<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\ClassLeak\Finder;

use RectorPrefix202608\Symfony\Component\Finder\Finder;
use RectorPrefix202608\Symfony\Component\Finder\SplFileInfo;
use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * @see \TomasVotruba\ClassLeak\Tests\Finder\PhpFilesFinderTest
 */
final class PhpFilesFinder
{
    /**
     * @param string[] $paths
     * @param string[] $fileExtensions
     * @param string[] $pathsToSkip
     *
     * @return string[]
     */
    public function findPhpFiles(array $paths, array $fileExtensions, array $pathsToSkip): array
    {
        Assert::allFileExists($paths);
        Assert::allString($fileExtensions);
        // skip-path option supports both directory names (e.g. "vendor") and
        // real/relative paths (e.g. "lib/vendor"). Symfony Finder's exclude()
        // matches relative paths from each searched root, so paths that include
        // the searched root prefix never match. Split them: pass simple
        // directory names to exclude(), and post-filter the rest by realpath.
        $excludedDirectoryNames = [];
        $excludedRealPaths = [];
        foreach ($pathsToSkip as $pathToSkip) {
            if (strpos($pathToSkip, '/') === \false && strpos($pathToSkip, '\\') === \false) {
                $excludedDirectoryNames[] = $pathToSkip;
                continue;
            }
            $realPath = realpath($pathToSkip);
            if ($realPath !== \false) {
                $excludedRealPaths[] = $realPath;
            }
        }
        // fallback to config paths
        $filePaths = [];
        $currentFileFinder = Finder::create()->files()->in($paths)->sortByName();
        if ($excludedDirectoryNames !== []) {
            $currentFileFinder->exclude($excludedDirectoryNames);
        }
        foreach ($fileExtensions as $fileExtension) {
            $currentFileFinder->name('*.' . $fileExtension);
        }
        foreach ($currentFileFinder as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            $realPath = $fileInfo->getRealPath();
            if ($this->isWithinExcludedPath($realPath, $excludedRealPaths)) {
                continue;
            }
            $filePaths[] = $realPath;
        }
        return $filePaths;
    }
    /**
     * @param string[] $excludedRealPaths
     */
    private function isWithinExcludedPath(string $realPath, array $excludedRealPaths): bool
    {
        foreach ($excludedRealPaths as $excludedRealPath) {
            if ($realPath === $excludedRealPath) {
                return \true;
            }
            if (strncmp($realPath, $excludedRealPath . \DIRECTORY_SEPARATOR, strlen($excludedRealPath . \DIRECTORY_SEPARATOR)) === 0) {
                return \true;
            }
        }
        return \false;
    }
}
