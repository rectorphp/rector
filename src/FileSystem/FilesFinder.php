<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
use Rector\Core\Configuration\Option;
use Rector\Core\Util\StringUtils;
use RectorPrefix20220209\Symfony\Component\Finder\Finder;
use RectorPrefix20220209\Symfony\Component\Finder\SplFileInfo;
use RectorPrefix20220209\Symplify\PackageBuilder\Parameter\ParameterProvider;
use RectorPrefix20220209\Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
use RectorPrefix20220209\Symplify\SmartFileSystem\FileSystemFilter;
use RectorPrefix20220209\Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Rector\Core\Tests\FileSystem\FilesFinder\FilesFinderTest
 */
final class FilesFinder
{
    /**
     * @var string
     * @see https://regex101.com/r/e1jm7v/1
     */
    private const STARTS_WITH_ASTERISK_REGEX = '#^\\*(.*?)[^*]$#';
    /**
     * @var string
     * @see https://regex101.com/r/EgJQyZ/1
     */
    private const ENDS_WITH_ASTERISK_REGEX = '#^[^*](.*?)\\*$#';
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilesystemTweaker
     */
    private $filesystemTweaker;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\Finder\FinderSanitizer
     */
    private $finderSanitizer;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\FileSystemFilter
     */
    private $fileSystemFilter;
    /**
     * @readonly
     * @var \Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver
     */
    private $skippedPathsResolver;
    /**
     * @readonly
     * @var \Rector\Caching\UnchangedFilesFilter
     */
    private $unchangedFilesFilter;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    public function __construct(\Rector\Core\FileSystem\FilesystemTweaker $filesystemTweaker, \RectorPrefix20220209\Symplify\SmartFileSystem\Finder\FinderSanitizer $finderSanitizer, \RectorPrefix20220209\Symplify\SmartFileSystem\FileSystemFilter $fileSystemFilter, \RectorPrefix20220209\Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver $skippedPathsResolver, \Rector\Caching\UnchangedFilesFilter $unchangedFilesFilter, \RectorPrefix20220209\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider)
    {
        $this->filesystemTweaker = $filesystemTweaker;
        $this->finderSanitizer = $finderSanitizer;
        $this->fileSystemFilter = $fileSystemFilter;
        $this->skippedPathsResolver = $skippedPathsResolver;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
        $this->parameterProvider = $parameterProvider;
    }
    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return SmartFileInfo[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes = []) : array
    {
        $filesAndDirectories = $this->filesystemTweaker->resolveWithFnmatch($source);
        $filePaths = $this->fileSystemFilter->filterFiles($filesAndDirectories);
        $directories = $this->fileSystemFilter->filterDirectories($filesAndDirectories);
        $smartFileInfos = [];
        foreach ($filePaths as $filePath) {
            $smartFileInfos[] = new \Symplify\SmartFileSystem\SmartFileInfo($filePath);
        }
        $smartFileInfos = $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($smartFileInfos);
        return \array_merge($smartFileInfos, $this->findInDirectories($directories, $suffixes));
    }
    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return SmartFileInfo[]
     */
    private function findInDirectories(array $directories, array $suffixes) : array
    {
        if ($directories === []) {
            return [];
        }
        $finder = \RectorPrefix20220209\Symfony\Component\Finder\Finder::create()->files()->size('> 0')->in($directories)->sortByName();
        if ($this->hasFollowLinks()) {
            $finder->followLinks();
        }
        if ($suffixes !== []) {
            $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);
            $finder->name($suffixesPattern);
        }
        $this->addFilterWithExcludedPaths($finder);
        $smartFileInfos = $this->finderSanitizer->sanitize($finder);
        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($smartFileInfos);
    }
    /**
     * @param string[] $suffixes
     */
    private function normalizeSuffixesToPattern(array $suffixes) : string
    {
        $suffixesPattern = \implode('|', $suffixes);
        return '#\\.(' . $suffixesPattern . ')$#';
    }
    private function addFilterWithExcludedPaths(\RectorPrefix20220209\Symfony\Component\Finder\Finder $finder) : void
    {
        $excludePaths = $this->skippedPathsResolver->resolve();
        if ($excludePaths === []) {
            return;
        }
        $finder->filter(function (\RectorPrefix20220209\Symfony\Component\Finder\SplFileInfo $splFileInfo) use($excludePaths) : bool {
            $realPath = $splFileInfo->getRealPath();
            if ($realPath === '') {
                // dead symlink
                return \false;
            }
            // make the path work accross different OSes
            $realPath = \str_replace('\\', '/', $realPath);
            // return false to remove file
            foreach ($excludePaths as $excludePath) {
                // make the path work accross different OSes
                $excludePath = \str_replace('\\', '/', $excludePath);
                if (\Rector\Core\Util\StringUtils::isMatch($realPath, '#' . \preg_quote($excludePath, '#') . '#')) {
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
        if (\Rector\Core\Util\StringUtils::isMatch($path, self::ENDS_WITH_ASTERISK_REGEX)) {
            return '*' . $path;
        }
        // starts with *
        if (\Rector\Core\Util\StringUtils::isMatch($path, self::STARTS_WITH_ASTERISK_REGEX)) {
            return $path . '*';
        }
        return $path;
    }
    private function hasFollowLinks() : bool
    {
        if (!$this->parameterProvider->hasParameter(\Rector\Core\Configuration\Option::FOLLOW_SYMLINKS)) {
            return \true;
        }
        return $this->parameterProvider->provideBoolParameter(\Rector\Core\Configuration\Option::FOLLOW_SYMLINKS);
    }
}
