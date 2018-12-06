<?php declare(strict_types=1);

namespace Rector\FileSystem;

use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symplify\PackageBuilder\FileSystem\FileSystem;
use Symplify\PackageBuilder\FileSystem\FinderSanitizer;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class FilesFinder
{
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
     * @var FileSystem
     */
    private $fileSystem;

    /**
     * @param string[] $excludePaths
     */
    public function __construct(
        array $excludePaths,
        FilesystemTweaker $filesystemTweaker,
        FinderSanitizer $finderSanitizer,
        FileSystem $fileSystem
    ) {
        $this->excludePaths = $excludePaths;
        $this->filesystemTweaker = $filesystemTweaker;
        $this->finderSanitizer = $finderSanitizer;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return SmartFileInfo[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes): array
    {
        $cacheKey = md5(serialize($source) . serialize($suffixes));
        if (isset($this->fileInfosBySourceAndSuffixes[$cacheKey])) {
            return $this->fileInfosBySourceAndSuffixes[$cacheKey];
        }

        [$files, $directories] = $this->fileSystem->separateFilesAndDirectories($source);

        $smartFileInfos = [];
        foreach ($files as $file) {
            $smartFileInfos[] = new SmartFileInfo($file);
        }

        $smartFileInfos = array_merge($smartFileInfos, $this->findInDirectories($directories, $suffixes));

        return $this->fileInfosBySourceAndSuffixes[$cacheKey] = $smartFileInfos;
    }

    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return SmartFileInfo[]
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
            ->exclude(
                ['examples', 'Examples', 'stubs', 'Stubs', 'fixtures', 'Fixtures', 'polyfill', 'Polyfill', 'vendor']
            )
            ->notName('*polyfill*')
            ->sortByName();

        $this->addFilterWithExcludedPaths($finder);

        return $this->finderSanitizer->sanitize($finder);
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

        $finder->filter(function (SplFileInfo $splFileInfo) {
            // return false to remove file
            foreach ($this->excludePaths as $excludePath) {
                if (Strings::match($splFileInfo->getRealPath(), '#' . preg_quote($excludePath, '#') . '#')) {
                    return false;
                }

                if (fnmatch($excludePath, $splFileInfo->getRealPath())) {
                    return false;
                }
            }

            return true;
        });
    }
}
