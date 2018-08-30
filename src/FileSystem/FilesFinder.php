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
        if (! $absoluteDirectories) {
            return [];
        }

        $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);

        $finder = Finder::create()
            ->files()
            ->in($absoluteDirectories)
            ->name($suffixesPattern)
            ->exclude(['examples', 'Examples', 'stubs', 'Stubs', 'fixtures', 'Fixtures', 'polyfill', 'Polyfill'])
            ->notName('*polyfill*');

        $this->addFilterWithExcludedPaths($finder);

        return iterator_to_array($finder->getIterator());
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
     * @todo decouple
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
                $absoluteDirectories = array_merge($absoluteDirectories, glob($directory, GLOB_ONLYDIR));
            } else { // is classic directory
                $this->ensureDirectoryExists($directory);
                $absoluteDirectories[] = $directory;
            }
        }

        return $absoluteDirectories;
    }

    private function addFilterWithExcludedPaths(Finder $finder): void
    {
        if (! $this->excludePaths) {
            return;
        }

        $finder->filter(function (NativeSplFileInfo $splFileInfo) {
            foreach ($this->excludePaths as $excludePath) {
                if (Strings::match($splFileInfo->getRealPath(), $excludePath)) {
                    return true;
                }

                return fnmatch($splFileInfo->getRealPath(), $excludePath);
            }

            return false;
        });
    }
}
