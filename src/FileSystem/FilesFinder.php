<?php declare(strict_types=1);

namespace Rector\FileSystem;

use Nette\Utils\Strings;
use Rector\Utils\FilesystemTweaker;
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
     * @var FilesystemTweaker
     */
    private $filesystemTweaker;

    /**
     * @param string[] $excludePaths
     */
    public function __construct(array $excludePaths, FilesystemTweaker $filesystemTweaker)
    {
        $this->excludePaths = $excludePaths;
        $this->filesystemTweaker = $filesystemTweaker;
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

        [$files, $directories] = $this->filesystemTweaker->splitSourceToDirectoriesAndFiles($source);

        $splFileInfos = [];
        foreach ($files as $file) {
            $splFileInfos[] = new SplFileInfo($file, '', '');
        }

        $splFileInfos = array_merge($splFileInfos, $this->findInDirectories($directories, $suffixes));

        return $this->fileInfosBySourceAndSuffixes[$cacheKey] = $splFileInfos;
    }

    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return SplFileInfo[]
     */
    private function findInDirectories(array $directories, array $suffixes): array
    {
        if (! count($directories)) {
            return [];
        }

        $absoluteDirectories = $this->filesystemTweaker->resolveDirectoriesWithFnmatch($directories);
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
        if (! $this->excludePaths) {
            return;
        }

        $finder->filter(function (NativeSplFileInfo $splFileInfo) {
            // return false to remove file
            foreach ($this->excludePaths as $excludePath) {
                if (Strings::match($splFileInfo->getRealPath(), '#' . preg_quote($excludePath, '#') . '#')) {
                    return false;
                }

                if (fnmatch($splFileInfo->getRealPath(), $excludePath)) {
                    return false;
                }
            }

            return true;
        });
    }
}
