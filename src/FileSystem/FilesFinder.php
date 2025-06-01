<?php

declare (strict_types=1);
namespace Rector\FileSystem;

use RectorPrefix202506\Nette\Utils\FileSystem;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\UnchangedFilesFilter;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Skipper\Skipper\PathSkipper;
use Rector\ValueObject\Configuration;
use RectorPrefix202506\Symfony\Component\Finder\Finder;
/**
 * @see \Rector\Tests\FileSystem\FilesFinder\FilesFinderTest
 */
final class FilesFinder
{
    /**
     * @readonly
     */
    private \Rector\FileSystem\FilesystemTweaker $filesystemTweaker;
    /**
     * @readonly
     */
    private UnchangedFilesFilter $unchangedFilesFilter;
    /**
     * @readonly
     */
    private \Rector\FileSystem\FileAndDirectoryFilter $fileAndDirectoryFilter;
    /**
     * @readonly
     */
    private PathSkipper $pathSkipper;
    /**
     * @readonly
     */
    private \Rector\FileSystem\FilePathHelper $filePathHelper;
    /**
     * @readonly
     */
    private ChangedFilesDetector $changedFilesDetector;
    public function __construct(\Rector\FileSystem\FilesystemTweaker $filesystemTweaker, UnchangedFilesFilter $unchangedFilesFilter, \Rector\FileSystem\FileAndDirectoryFilter $fileAndDirectoryFilter, PathSkipper $pathSkipper, \Rector\FileSystem\FilePathHelper $filePathHelper, ChangedFilesDetector $changedFilesDetector)
    {
        $this->filesystemTweaker = $filesystemTweaker;
        $this->unchangedFilesFilter = $unchangedFilesFilter;
        $this->fileAndDirectoryFilter = $fileAndDirectoryFilter;
        $this->pathSkipper = $pathSkipper;
        $this->filePathHelper = $filePathHelper;
        $this->changedFilesDetector = $changedFilesDetector;
    }
    /**
     * @param string[] $source
     * @param string[] $suffixes
     * @return string[]
     */
    public function findInDirectoriesAndFiles(array $source, array $suffixes = [], bool $sortByName = \true, ?string $onlySuffix = null) : array
    {
        $filesAndDirectories = $this->filesystemTweaker->resolveWithFnmatch($source);
        // filtering files in files collection
        $filteredFilePaths = $this->fileAndDirectoryFilter->filterFiles($filesAndDirectories);
        $filteredFilePaths = \array_filter($filteredFilePaths, fn(string $filePath): bool => !$this->pathSkipper->shouldSkip($filePath));
        // fallback append `.php` to be used for both $filteredFilePaths and $filteredFilePathsInDirectories
        $hasOnlySuffix = $onlySuffix !== null && $onlySuffix !== '';
        if ($hasOnlySuffix && \substr_compare($onlySuffix, '.php', -\strlen('.php')) !== 0) {
            $onlySuffix .= '.php';
        }
        // filter files by specific suffix
        if ($hasOnlySuffix) {
            /** @var string $onlySuffix */
            $fileWithSuffixFilter = static fn(string $filePath): bool => \substr_compare($filePath, $onlySuffix, -\strlen($onlySuffix)) === 0;
        } elseif ($suffixes !== []) {
            $fileWithSuffixFilter = static function (string $filePath) use($suffixes) : bool {
                $filePathExtension = \pathinfo($filePath, \PATHINFO_EXTENSION);
                return \in_array($filePathExtension, $suffixes, \true);
            };
        } else {
            $fileWithSuffixFilter = fn(): bool => \true;
        }
        $filteredFilePaths = \array_filter($filteredFilePaths, $fileWithSuffixFilter === null ? fn($value, $key): bool => !empty($value) : $fileWithSuffixFilter, $fileWithSuffixFilter === null ? \ARRAY_FILTER_USE_BOTH : 0);
        // add file without extension after file extension filter
        $filteredFilePaths = \array_merge($filteredFilePaths, SimpleParameterProvider::provideArrayParameter(Option::FILES_WITHOUT_EXTENSION));
        $filteredFilePaths = \array_filter($filteredFilePaths, function (string $file) : bool {
            if ($this->isStartWithShortPHPTag(FileSystem::read($file))) {
                SimpleParameterProvider::addParameter(Option::SKIPPED_START_WITH_SHORT_OPEN_TAG_FILES, $this->filePathHelper->relativePath($file));
                return \false;
            }
            return \true;
        });
        // filtering files in directories collection
        $directories = $this->fileAndDirectoryFilter->filterDirectories($filesAndDirectories);
        $filteredFilePathsInDirectories = $this->findInDirectories($directories, $suffixes, $hasOnlySuffix, $onlySuffix, $sortByName);
        $filePaths = \array_merge($filteredFilePaths, $filteredFilePathsInDirectories);
        return $this->unchangedFilesFilter->filterFilePaths($filePaths);
    }
    /**
     * @param string[] $paths
     * @return string[]
     */
    public function findFilesInPaths(array $paths, Configuration $configuration) : array
    {
        if ($configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
        }
        return $this->findInDirectoriesAndFiles($paths, $configuration->getFileExtensions(), \true, $configuration->getOnlySuffix());
    }
    /**
     * Exclude short "<?=" tags as lead to invalid changes
     */
    private function isStartWithShortPHPTag(string $fileContent) : bool
    {
        return \strncmp(\ltrim($fileContent), '<?=', \strlen('<?=')) === 0;
    }
    /**
     * @param string[] $directories
     * @param string[] $suffixes
     * @return string[]
     */
    private function findInDirectories(array $directories, array $suffixes, bool $hasOnlySuffix, ?string $onlySuffix = null, bool $sortByName = \true) : array
    {
        if ($directories === []) {
            return [];
        }
        $finder = Finder::create()->files()->size('> 0')->in($directories);
        // filter files by specific suffix
        if ($hasOnlySuffix) {
            $finder->name('*' . $onlySuffix);
        } elseif ($suffixes !== []) {
            $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);
            $finder->name($suffixesPattern);
        }
        if ($sortByName) {
            $finder->sortByName();
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
            if ($this->isStartWithShortPHPTag($fileInfo->getContents())) {
                SimpleParameterProvider::addParameter(Option::SKIPPED_START_WITH_SHORT_OPEN_TAG_FILES, $this->filePathHelper->relativePath($path));
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
