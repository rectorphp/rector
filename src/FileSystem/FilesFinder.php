<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Nette\Caching\Cache;
use Nette\Utils\Strings;
use Rector\Core\Configuration\Configuration;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
use Symplify\SmartFileSystem\FileSystemFilter;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
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
    private const STARTS_WITH_ASTERISK_REGEX = '#^\*(.*?)[^*]$#';

    /**
     * @var string
     * @see https://regex101.com/r/EgJQyZ/1
     */
    private const ENDS_WITH_ASTERISK_REGEX = '#^[^*](.*?)\*$#';

    /**
     * @var FilesystemTweaker
     */
    private $filesystemTweaker;

    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;

    /**
     * @var FileSystemFilter
     */
    private $fileSystemFilter;

    /**
     * @var SkippedPathsResolver
     */
    private $skippedPathsResolver;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(
        FilesystemTweaker $filesystemTweaker,
        FinderSanitizer $finderSanitizer,
        FileSystemFilter $fileSystemFilter,
        SkippedPathsResolver $skippedPathsResolver,
        Configuration $configuration,
        Cache $cache
    ) {
        $this->filesystemTweaker = $filesystemTweaker;
        $this->finderSanitizer = $finderSanitizer;
        $this->fileSystemFilter = $fileSystemFilter;
        $this->skippedPathsResolver = $skippedPathsResolver;
        $this->configuration = $configuration;
        $this->cache = $cache;
    }

    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return SmartFileInfo[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes): array
    {
        if (! $this->configuration->isCacheEnabled() || $this->configuration->shouldClearCache()) {
            $this->cache->clean([
                Cache::ALL => true,
            ]);

            return $this->collectFileInfos($source, $suffixes);
        }

        $cacheKey = md5(serialize($source) . serialize($suffixes));
        $loadCache = $this->cache->load($cacheKey);

        if ($loadCache) {
            return $this->getSmartFileInfosFromStringFiles($loadCache);
        }

        $files = $this->fileSystemFilter->filterFiles($source);
        $directories = $this->fileSystemFilter->filterDirectories($source);

        $files = array_merge($files, $this->findInDirectories($directories, $suffixes));
        $this->cache->save($cacheKey, $files);

        return $this->getSmartFileInfosFromStringFiles($files);
    }

    /**
     * @param string[] $files
     * @return SmartFileInfo[]
     */
    private function getSmartFileInfosFromStringFiles(array $files): array
    {
        $smartFileInfos = [];
        foreach ($files as $file) {
            $smartFileInfos[] = new SmartFileInfo($file);
        }

        return $smartFileInfos;
    }

    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return SmartFileInfo[]
     */
    private function collectFileInfos(array $source, array $suffixes): array
    {
        $files = $this->fileSystemFilter->filterFiles($source);
        $directories = $this->fileSystemFilter->filterDirectories($source);

        $smartFileInfos = [];
        foreach ($files as $file) {
            $smartFileInfos[] = new SmartFileInfo($file);
        }

        return array_merge(
            $smartFileInfos,
            $this->getSmartFileInfosFromStringFiles($this->findInDirectories($directories, $suffixes))
        );
    }

    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return string[]
     */
    private function findInDirectories(array $directories, array $suffixes): array
    {
        if ($directories === []) {
            return [];
        }

        $absoluteDirectories = $this->filesystemTweaker->resolveDirectoriesWithFnmatch($directories);
        if ($absoluteDirectories === []) {
            return [];
        }

        $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);

        $finder = Finder::create()
            ->followLinks()
            ->files()
            // skip empty files
            ->size('> 0')
            ->in($absoluteDirectories)
            ->name($suffixesPattern)
            ->sortByName();

        $this->addFilterWithExcludedPaths($finder);

        $smartFileInfos = $this->finderSanitizer->sanitize($finder);

        $files = [];
        foreach ($smartFileInfos as $smartFileInfo) {
            $files[] = $smartFileInfo->getRelativeFilePathFromCwd();
        }

        return $files;
    }

    /**
     * @param string[] $suffixes
     */
    private function normalizeSuffixesToPattern(array $suffixes): string
    {
        $suffixesPattern = implode('|', $suffixes);
        return '#\.(' . $suffixesPattern . ')$#';
    }

    private function addFilterWithExcludedPaths(Finder $finder): void
    {
        $excludePaths = $this->skippedPathsResolver->resolve();
        if ($excludePaths === []) {
            return;
        }

        $finder->filter(function (SplFileInfo $splFileInfo) use ($excludePaths): bool {
            /** @var string|false $realPath */
            $realPath = $splFileInfo->getRealPath();
            if (! $realPath) {
                //dead symlink
                return false;
            }

            // make the path work accross different OSes
            $realPath = str_replace('\\', '/', $realPath);

            // return false to remove file
            foreach ($excludePaths as $excludePath) {
                // make the path work accross different OSes
                $excludePath = str_replace('\\', '/', $excludePath);

                if (Strings::match($realPath, '#' . preg_quote($excludePath, '#') . '#')) {
                    return false;
                }

                $excludePath = $this->normalizeForFnmatch($excludePath);
                if (fnmatch($excludePath, $realPath)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * "value*" → "*value*"
     * "*value" → "*value*"
     */
    private function normalizeForFnmatch(string $path): string
    {
        // ends with *
        if (Strings::match($path, self::ENDS_WITH_ASTERISK_REGEX)) {
            return '*' . $path;
        }

        // starts with *
        if (Strings::match($path, self::STARTS_WITH_ASTERISK_REGEX)) {
            return $path . '*';
        }

        return $path;
    }
}
