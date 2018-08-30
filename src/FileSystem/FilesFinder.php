<?php declare(strict_types=1);

namespace Rector\FileSystem;

use Nette\Utils\Strings;
use Rector\Exception\FileSystem\DirectoryNotFoundException;
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

        $files = [];
        $directories = [];

        foreach ($source as $singleSource) {
            if (is_file($singleSource)) {
                $files[] = new SplFileInfo($singleSource, '', '');
            } else {
                $directories[] = $singleSource;
            }
        }

        if (count($directories)) {
            $files = array_merge($files, $this->findInDirectories($directories, $suffixes));
        }

        return $this->fileInfosBySourceAndSuffixes[$cacheKey] = $files;
    }

    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return SplFileInfo[]
     */
    private function findInDirectories(array $directories, array $suffixes): array
    {
        $this->ensureDirectoriesExist($directories);

        $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);

        $finder = Finder::create()
            ->files()
            ->name($suffixesPattern)
            ->in($directories)
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

    /**
     * @param string[] $directories
     */
    private function ensureDirectoriesExist(array $directories): void
    {
        foreach ($directories as $directory) {
            if (file_exists($directory)) {
                continue;
            }

            throw new DirectoryNotFoundException(sprintf('Directory "%s" was not found.', $directory));
        }
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
}
