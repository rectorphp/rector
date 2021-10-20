<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Symplify\SmartFileSystem\Finder;

use RectorPrefix20211020\Symfony\Component\Finder\Finder;
use RectorPrefix20211020\Symplify\SmartFileSystem\FileSystemFilter;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @api
 * @see \Symplify\SmartFileSystem\Tests\Finder\SmartFinder\SmartFinderTest
 */
final class SmartFinder
{
    /**
     * @var \Symplify\SmartFileSystem\Finder\FinderSanitizer
     */
    private $finderSanitizer;
    /**
     * @var \Symplify\SmartFileSystem\FileSystemFilter
     */
    private $fileSystemFilter;
    public function __construct(\RectorPrefix20211020\Symplify\SmartFileSystem\Finder\FinderSanitizer $finderSanitizer, \RectorPrefix20211020\Symplify\SmartFileSystem\FileSystemFilter $fileSystemFilter)
    {
        $this->finderSanitizer = $finderSanitizer;
        $this->fileSystemFilter = $fileSystemFilter;
    }
    /**
     * @param string[] $directoriesOrFiles
     * @return SmartFileInfo[]
     */
    public function findPaths(array $directoriesOrFiles, string $path) : array
    {
        $directories = $this->fileSystemFilter->filterDirectories($directoriesOrFiles);
        $fileInfos = [];
        if ($directories !== []) {
            $finder = new \RectorPrefix20211020\Symfony\Component\Finder\Finder();
            $finder->name('*')->in($directories)->path($path)->files()->sortByName();
            $fileInfos = $this->finderSanitizer->sanitize($finder);
        }
        return $fileInfos;
    }
    /**
     * @param string[] $directoriesOrFiles
     * @param string[] $excludedDirectories
     * @return SmartFileInfo[]
     */
    public function find(array $directoriesOrFiles, string $name, array $excludedDirectories = []) : array
    {
        $directories = $this->fileSystemFilter->filterDirectories($directoriesOrFiles);
        $fileInfos = [];
        if ($directories !== []) {
            $finder = new \RectorPrefix20211020\Symfony\Component\Finder\Finder();
            $finder->name($name)->in($directories)->files()->sortByName();
            if ($excludedDirectories !== []) {
                $finder->exclude($excludedDirectories);
            }
            $fileInfos = $this->finderSanitizer->sanitize($finder);
        }
        $files = $this->fileSystemFilter->filterFiles($directoriesOrFiles);
        foreach ($files as $file) {
            $fileInfos[] = new \Symplify\SmartFileSystem\SmartFileInfo($file);
        }
        return $fileInfos;
    }
}
