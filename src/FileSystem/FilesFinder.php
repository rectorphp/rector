<?php

declare (strict_types=1);
namespace Rector\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
use Rector\Skipper\Skipper\PathSkipper;
use RectorPrefix202402\Symfony\Component\Finder\Finder;
/**
 * @see \Rector\Tests\FileSystem\FilesFinder\FilesFinderTest
 */
final class FilesFinder
{
    /**
     * @readonly
     * @var \Rector\FileSystem\FilesystemTweaker
     */
    private $filesystemTweaker;
    /**
     * @readonly
     * @var \Rector\Caching\UnchangedFilesFilter
     */
    private $unchangedFilesFilter;
    /**
     * @readonly
     * @var \Rector\FileSystem\FileAndDirectoryFilter
     */
    private $fileAndDirectoryFilter;
    /**
     * @readonly
     * @var \Rector\Skipper\Skipper\PathSkipper
     */
    private $pathSkipper;
    public function __construct(\Rector\FileSystem\FilesystemTweaker $filesystemTweaker, UnchangedFilesFilter $unchangedFilesFilter, \Rector\FileSystem\FileAndDirectoryFilter $fileAndDirectoryFilter, PathSkipper $pathSkipper)
    {
        $this->filesystemTweaker = $filesystemTweaker;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
        $this->fileAndDirectoryFilter = $fileAndDirectoryFilter;
        $this->pathSkipper = $pathSkipper;
    }
    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return string[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes = [], bool $sortByName = \true) : array
    {
        $filesAndDirectories = $this->filesystemTweaker->resolveWithFnmatch($source);
        $files = $this->fileAndDirectoryFilter->filterFiles($filesAndDirectories);
        $filteredFilePaths = \array_filter($files, function (string $filePath) : bool {
            return !$this->pathSkipper->shouldSkip($filePath);
        });
        if ($suffixes !== []) {
            $fileWithExtensionsFilter = static function (string $filePath) use($suffixes) : bool {
                $filePathExtension = \pathinfo($filePath, \PATHINFO_EXTENSION);
                return \in_array($filePathExtension, $suffixes, \true);
            };
            $filteredFilePaths = \array_filter($filteredFilePaths, $fileWithExtensionsFilter);
        }
        $directories = $this->fileAndDirectoryFilter->filterDirectories($filesAndDirectories);
        $filteredFilePathsInDirectories = $this->findInDirectories($directories, $suffixes, $sortByName);
        $filePaths = \array_merge($filteredFilePaths, $filteredFilePathsInDirectories);
        return $this->unchangedFilesFilter->filterFileInfos($filePaths);
    }
    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return string[]
     */
    private function findInDirectories(array $directories, array $suffixes, bool $sortByName = \true) : array
    {
        if ($directories === []) {
            return [];
        }
        $finder = Finder::create()->files()->size('> 0')->in($directories);
        if ($sortByName) {
            $finder->sortByName();
        }
        if ($suffixes !== []) {
            $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);
            $finder->name($suffixesPattern);
        }
        $filePaths = [];
        foreach ($finder as $fileInfo) {
            // getRealPath() function will return false when it checks broken symlinks.
            // So we should check if this file exists or we got broken symlink
            /** @var string|false $path */
            $path = $fileInfo->getRealPath();
            if ($path === \false) {
                continue;
            }
            if ($this->pathSkipper->shouldSkip($path)) {
                continue;
            }
            $filePaths[] = $path;
        }
        return $filePaths;
    }
    /**
     * @param string[] $suffixes
     */
    private function normalizeSuffixesToPattern(array $suffixes) : string
    {
        $suffixesPattern = \implode('|', $suffixes);
        return '#\\.(' . $suffixesPattern . ')$#';
    }
}
