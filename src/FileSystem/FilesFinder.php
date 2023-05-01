<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
use Rector\Core\Util\StringUtils;
use Rector\Skipper\Enum\AsteriskMatch;
use Rector\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
use RectorPrefix202305\Symfony\Component\Finder\Finder;
use RectorPrefix202305\Symfony\Component\Finder\SplFileInfo;
/**
 * @see \Rector\Core\Tests\FileSystem\FilesFinder\FilesFinderTest
 */
final class FilesFinder
{
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilesystemTweaker
     */
    private $filesystemTweaker;
    /**
     * @readonly
     * @var \Rector\Skipper\SkipCriteriaResolver\SkippedPathsResolver
     */
    private $skippedPathsResolver;
    /**
     * @readonly
     * @var \Rector\Caching\UnchangedFilesFilter
     */
    private $unchangedFilesFilter;
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FileAndDirectoryFilter
     */
    private $fileAndDirectoryFilter;
    public function __construct(\Rector\Core\FileSystem\FilesystemTweaker $filesystemTweaker, SkippedPathsResolver $skippedPathsResolver, UnchangedFilesFilter $unchangedFilesFilter, \Rector\Core\FileSystem\FileAndDirectoryFilter $fileAndDirectoryFilter)
    {
        $this->filesystemTweaker = $filesystemTweaker;
        $this->skippedPathsResolver = $skippedPathsResolver;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
        $this->fileAndDirectoryFilter = $fileAndDirectoryFilter;
    }
    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return string[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes = []) : array
    {
        $filesAndDirectories = $this->filesystemTweaker->resolveWithFnmatch($source);
        $filePaths = $this->fileAndDirectoryFilter->filterFiles($filesAndDirectories);
        $directories = $this->fileAndDirectoryFilter->filterDirectories($filesAndDirectories);
        $currentAndDependentFilePaths = $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($filePaths);
        return \array_merge($currentAndDependentFilePaths, $this->findInDirectories($directories, $suffixes));
    }
    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return string[]
     */
    private function findInDirectories(array $directories, array $suffixes) : array
    {
        if ($directories === []) {
            return [];
        }
        $finder = Finder::create()->files()->size('> 0')->in($directories)->sortByName();
        if ($suffixes !== []) {
            $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);
            $finder->name($suffixesPattern);
        }
        $this->addFilterWithExcludedPaths($finder);
        $filePaths = [];
        foreach ($finder as $fileInfo) {
            // getRealPath() function will return false when it checks broken symlinks.
            // So we should check if this file exists or we got broken symlink
            /** @var string|false $path */
            $path = $fileInfo->getRealPath();
            if ($path !== \false) {
                $filePaths[] = $path;
            }
        }
        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($filePaths);
    }
    /**
     * @param string[] $suffixes
     */
    private function normalizeSuffixesToPattern(array $suffixes) : string
    {
        $suffixesPattern = \implode('|', $suffixes);
        return '#\\.(' . $suffixesPattern . ')$#';
    }
    private function addFilterWithExcludedPaths(Finder $finder) : void
    {
        $excludePaths = $this->skippedPathsResolver->resolve();
        if ($excludePaths === []) {
            return;
        }
        $finder->filter(function (SplFileInfo $splFileInfo) use($excludePaths) : bool {
            /** @var string|false $realPath */
            $realPath = $splFileInfo->getRealPath();
            if ($realPath === \false) {
                // dead symlink
                return \false;
            }
            // make the path work accross different OSes
            $realPath = \str_replace('\\', '/', $realPath);
            // return false to remove file
            foreach ($excludePaths as $excludePath) {
                // make the path work accross different OSes
                $excludePath = \str_replace('\\', '/', $excludePath);
                if (StringUtils::isMatch($realPath, '#' . \preg_quote($excludePath, '#') . '#')) {
                    return \false;
                }
                $excludePath = $this->normalizeForFnmatch($excludePath);
                if (\fnmatch($excludePath, $realPath)) {
                    return \false;
                }
            }
            return \true;
        });
    }
    /**
     * "value*" → "*value*"
     * "*value" → "*value*"
     */
    private function normalizeForFnmatch(string $path) : string
    {
        // ends with *
        if (StringUtils::isMatch($path, AsteriskMatch::ONLY_ENDS_WITH_ASTERISK_REGEX)) {
            return '*' . $path;
        }
        // starts with *
        if (StringUtils::isMatch($path, AsteriskMatch::ONLY_STARTS_WITH_ASTERISK_REGEX)) {
            return $path . '*';
        }
        return $path;
    }
}
