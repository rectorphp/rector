<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Nette\Utils\Strings;
use Rector\Core\Configuration\Option;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
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
     * @var SmartFileInfo[][]
     */
    private $fileInfosBySourceAndSuffixes = [];

    /**
     * @var string[]
     */
    private $excludePaths = [];

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

    public function __construct(
        FilesystemTweaker $filesystemTweaker,
        FinderSanitizer $finderSanitizer,
        FileSystemFilter $fileSystemFilter,
        ParameterProvider $parameterProvider
    ) {
        $this->excludePaths = (array) $parameterProvider->provideParameter(Option::EXCLUDE_PATHS);
        $this->filesystemTweaker = $filesystemTweaker;
        $this->finderSanitizer = $finderSanitizer;
        $this->fileSystemFilter = $fileSystemFilter;
    }

    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return SmartFileInfo[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes, bool $matchDiff = false): array
    {
        $cacheKey = md5(serialize($source) . serialize($suffixes) . (int) $matchDiff);
        if (isset($this->fileInfosBySourceAndSuffixes[$cacheKey])) {
            return $this->fileInfosBySourceAndSuffixes[$cacheKey];
        }

        $files = $this->fileSystemFilter->filterFiles($source);
        $directories = $this->fileSystemFilter->filterDirectories($source);

        $smartFileInfos = [];
        foreach ($files as $file) {
            $smartFileInfos[] = new SmartFileInfo($file);
        }

        $smartFileInfos = array_merge($smartFileInfos, $this->findInDirectories($directories, $suffixes));

        if ($matchDiff) {
            $gitDiffFiles = $this->getGitDiff();

            $smartFileInfos = array_filter($smartFileInfos, function (SmartFileInfo $fileInfo) use (
                $gitDiffFiles
            ): bool {
                return in_array($fileInfo->getRealPath(), $gitDiffFiles, true);
            });

            $smartFileInfos = array_values($smartFileInfos);
        }

        return $this->fileInfosBySourceAndSuffixes[$cacheKey] = $smartFileInfos;
    }

    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return SmartFileInfo[]
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
            ->in($absoluteDirectories)
            ->name($suffixesPattern)
            ->sortByName();

        $this->addFilterWithExcludedPaths($finder);

        return $this->finderSanitizer->sanitize($finder);
    }

    /**
     * @return string[] The absolute path to the file matching the git diff shell command.
     */
    private function getGitDiff(): array
    {
        $plainDiff = shell_exec('git diff --name-only') ?: '';
        $relativePaths = explode(PHP_EOL, trim($plainDiff));

        return array_values(array_filter(array_map('realpath', $relativePaths)));
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
        if ($this->excludePaths === []) {
            return;
        }

        $finder->filter(function (SplFileInfo $splFileInfo): bool {
            /** @var string|false $realPath */
            $realPath = $splFileInfo->getRealPath();
            if (! $realPath) {
                //dead symlink
                return false;
            }

            // make the path work accross different OSes
            $realPath = str_replace('\\', '/', $realPath);

            // return false to remove file
            foreach ($this->excludePaths as $excludePath) {
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
