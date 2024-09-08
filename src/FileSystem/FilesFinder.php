<?php

declare (strict_types=1);
namespace Rector\FileSystem;

use RectorPrefix202409\Nette\Utils\FileSystem;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Caching\UnchangedFilesFilter;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Skipper\Skipper\PathSkipper;
use Rector\ValueObject\Configuration;
use RectorPrefix202409\Symfony\Component\Finder\Finder;
/**
 * @see \Rector\Tests\FileSystem\FilesFinder\FilesFinderTest
 */
final class FilesFinder
{
    /**
     * @readonly
     * @var \Rector\FileSystem\FilesystemTweaker
     */
    private $filesystemTweaker;
    /**
     * @readonly
     * @var \Rector\Caching\UnchangedFilesFilter
     */
    private $unchangedFilesFilter;
    /**
     * @readonly
     * @var \Rector\FileSystem\FileAndDirectoryFilter
     */
    private $fileAndDirectoryFilter;
    /**
     * @readonly
     * @var \Rector\Skipper\Skipper\PathSkipper
     */
    private $pathSkipper;
    /**
     * @readonly
     * @var \Rector\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
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
    public function findInDirectoriesAndFiles(array $source, array $suffixes = [], bool $sortByName = \true) : array
    {
        $filesAndDirectories = $this->filesystemTweaker->resolveWithFnmatch($source);
        // filtering files in files collection
        $filteredFilePaths = $this->fileAndDirectoryFilter->filterFiles($filesAndDirectories);
        $filteredFilePaths = \array_map(function (string $filePath) : string {
            return \realpath($filePath);
        }, $filteredFilePaths);
        $filteredFilePaths = \array_filter($filteredFilePaths, function (string $filePath) : bool {
            return !$this->pathSkipper->shouldSkip($filePath);
        });
        if ($suffixes !== []) {
            $fileWithExtensionsFilter = static function (string $filePath) use($suffixes) : bool {
                $filePathExtension = \pathinfo($filePath, \PATHINFO_EXTENSION);
                return \in_array($filePathExtension, $suffixes, \true);
            };
            $filteredFilePaths = \array_filter($filteredFilePaths, $fileWithExtensionsFilter);
        }
        $filteredFilePaths = \array_filter($filteredFilePaths, function (string $file) : bool {
            if ($this->isStartWithShortPHPTag(FileSystem::read($file))) {
                SimpleParameterProvider::addParameter(Option::SKIPPED_START_WITH_SHORT_OPEN_TAG_FILES, $this->filePathHelper->relativePath($file));
                return \false;
            }
            return \true;
        });
        // filtering files in directories collection
        $directories = $this->fileAndDirectoryFilter->filterDirectories($filesAndDirectories);
        $filteredFilePathsInDirectories = $this->findInDirectories($directories, $suffixes, $sortByName);
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
        $supportedFileExtensions = $configuration->getFileExtensions();
        return $this->findInDirectoriesAndFiles($paths, $supportedFileExtensions);
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
    private function findInDirectories(array $directories, array $suffixes, bool $sortByName = \true) : array
    {
        if ($directories === []) {
            return [];
        }
        $finder = Finder::create()->files()->size('> 0')->in($directories);
        if ($sortByName) {
            $finder->sortByName();
        }
        if ($suffixes !== []) {
            $suffixesPattern = $this->normalizeSuffixesToPattern($suffixes);
            $finder->name($suffixesPattern);
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
