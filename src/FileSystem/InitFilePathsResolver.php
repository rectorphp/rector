<?php

declare (strict_types=1);
namespace Rector\FileSystem;

use RectorPrefix202506\Symfony\Component\Finder\Finder;
use RectorPrefix202506\Symfony\Component\Finder\SplFileInfo;
/**
 * @see \Rector\Tests\FileSystem\InitFilePathsResolver\InitFilePathsResolverTest
 */
final class InitFilePathsResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/XkQ6Pe/1
     */
    private const DO_NOT_INCLUDE_PATHS_REGEX = '#(vendor|var|stubs|temp|templates|tmp|e2e|bin|build|Migrations|data(?:base)?|storage|migrations|writable|node_modules)#';
    /**
     * @return string[]
     */
    public function resolve(string $projectDirectory) : array
    {
        $rootDirectoryFinder = Finder::create()->directories()->depth(0)->notPath(self::DO_NOT_INCLUDE_PATHS_REGEX)->in($projectDirectory)->sortByName();
        /** @var SplFileInfo[] $rootDirectoryFileInfos */
        $rootDirectoryFileInfos = \iterator_to_array($rootDirectoryFinder);
        $projectDirectories = [];
        foreach ($rootDirectoryFileInfos as $rootDirectoryFileInfo) {
            if (!$this->hasDirectoryFileInfoPhpFiles($rootDirectoryFileInfo)) {
                continue;
            }
            $projectDirectories[] = $rootDirectoryFileInfo->getRelativePathname();
        }
        return $projectDirectories;
    }
    private function hasDirectoryFileInfoPhpFiles(SplFileInfo $rootDirectoryFileInfo) : bool
    {
        // is directory with PHP files?
        return Finder::create()->files()->in($rootDirectoryFileInfo->getPathname())->name('*.php')->hasResults();
    }
}
