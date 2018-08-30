<?php declare(strict_types=1);

namespace Rector\FileSystem;

use Nette\Utils\Strings;
use Rector\Exception\FileSystem\DirectoryNotFoundException;
use SplFileInfo as NativeSplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class FilesFinder
{
    /**
     * @var SplFileInfo[][]
     */
    private $fileInfosBySourceAndSuffixes = [];

    /**
     * @var string[]
     */
    private $excludePaths = [];

    /**
     * @param string[] $excludePaths
     */
    public function __construct(array $excludePaths)
    {
        $this->excludePaths = $excludePaths;
    }

    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return SplFileInfo[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes): array
    {
        $cacheKey = md5(serialize($source) . serialize($suffixes));
        if (isset($this->fileInfosBySourceAndSuffixes[$cacheKey])) {
            return $this->fileInfosBySourceAndSuffixes[$cacheKey];
        }

        [$splFileInfos, $directories] = $this->splitSourceToDirectoriesAndFiles($source);
        if (count($directories)) {
            $splFileInfos = array_merge($splFileInfos, $this->findInDirectories($directories, $suffixes));
        }

        return $this->fileInfosBySourceAndSuffixes[$cacheKey] = $splFileInfos;
    }

    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return SplFileInfo[]
     */
    private function findInDirectories(array $directories, array $suffixes): array
    {
        $absoluteDirectories = $this->resolveAbsoluteDirectories($directories);
        $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);

        $finder = Finder::create()
            ->files()
            ->in($absoluteDirectories)
            ->name($suffixesPattern)
            ->exclude(['examples', 'Examples', 'stubs', 'Stubs', 'fixtures', 'Fixtures', 'polyfill', 'Polyfill'])
            ->notName('*polyfill*');

        $splFileInfos = iterator_to_array($finder->getIterator());
        if (! $this->excludePaths) {
            return $splFileInfos;
        }

        // to overcome magic behavior: https://github.com/symfony/symfony/pull/26396/files
        /** @var SplFileInfo[] $splFileInfos */
        return $this->filterOutFilesByPatterns($splFileInfos, $this->excludePaths);
    }

    private function ensureFileExists(string $file): void
    {
        if (file_exists($file)) {
            return;
        }

        throw new DirectoryNotFoundException(sprintf('File "%s" was not found.', $file));
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (file_exists($directory)) {
            return;
        }

        throw new DirectoryNotFoundException(sprintf('Directory "%s" was not found.', $directory));
    }

    /**
     * @param string[] $suffixes
     */
    private function normalizeSuffixesToPattern(array $suffixes): string
    {
        $suffixesPattern = implode('|', $suffixes);

        return '#\.(' . $suffixesPattern . ')$#';
    }

    /**
     * @param SplFileInfo[] $splFileInfos
     * @param string[] $patternsToExclude
     * @return SplFileInfo[]
     */
    private function filterOutFilesByPatterns(array $splFileInfos, array $patternsToExclude): array
    {
        $filteredFiles = [];

        foreach ($splFileInfos as $relativePath => $splFileInfo) {
            foreach ($patternsToExclude as $patternToExclude) {
                if (Strings::match($splFileInfo->getRealPath(), $patternToExclude)) {
                    continue;
                }

                if (fnmatch($splFileInfo->getRealPath(), $patternToExclude)) {
                    continue;
                }

                $filteredFiles[$relativePath] = $splFileInfo;
            }
        }

        return $filteredFiles;

    }

    /**
     * @param string[] $source
     * @return string[][]|SplFileInfo[]
     */
    private function splitSourceToDirectoriesAndFiles(array $source): array
    {
        $files = [];
        $directories = [];

        foreach ($source as $singleSource) {
            if (is_file($singleSource)) {
                $this->ensureFileExists($singleSource);
                $files[] = new SplFileInfo($singleSource, '', '');
            } else {
                $directories[] = $singleSource;
            }
        }

        return [$files, $directories];
    }

    /**
     * @param string[] $directories
     * @return string[]
     */
    private function resolveAbsoluteDirectories(array $directories): array
    {
        $absoluteDirectories = [];

        foreach ($directories as $directory) {
            if (Strings::contains($directory, '*')) { // is fnmatch for directories
                $directoriesFinder = Finder::create()
                    ->in(getcwd())
                    ->directories()
                    ->filter(function (NativeSplFileInfo $splFileInfo) use ($directory) {
                        // keep only file that match specific pattern
                        return fnmatch('*' . $directory . '*', $splFileInfo->getRealPath());
                    });

                $absoluteDirectories = array_merge(
                    $absoluteDirectories,
                    iterator_to_array($directoriesFinder->getIterator())
                );
            } else { // is classic directory
                $this->ensureDirectoryExists($directory);
                $absoluteDirectories[] = $directory;
            }
        }

        return $absoluteDirectories;
    }
}
