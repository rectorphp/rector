<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use RectorPrefix202308\Symfony\Component\Finder\Finder;
use RectorPrefix202308\Symfony\Component\Finder\SplFileInfo;
/**
 * @see \Rector\Core\Tests\FileSystem\InitFilePathsResolver\InitFilePathsResolverTest
 */
final class InitFilePathsResolver
{
    /**
     * @return string[]
     */
    public function resolve(string $projectDirectory) : array
    {
        $rootDirectoryFinder = Finder::create()->directories()->depth(0)->notPath('#(vendor|var|stubs|temp|templates|tmp|e2e|bin|build|Migrations|database|storage|migrations)#')->in($projectDirectory)->sortByName();
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
        $phpFilesCount = Finder::create()->files()->in($rootDirectoryFileInfo->getPathname())->name('*.php')->count();
        return $phpFilesCount !== 0;
    }
}
