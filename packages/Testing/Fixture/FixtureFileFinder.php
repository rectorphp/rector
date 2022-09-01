<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use Iterator;
use RectorPrefix202209\Symfony\Component\Finder\Finder;
use RectorPrefix202209\Symfony\Component\Finder\SplFileInfo;
final class FixtureFileFinder
{
    /**
     * @return Iterator<array<int, string>>
     */
    public static function yieldFilePathsFromDirectory(string $directory, string $suffix = '*.php.inc') : Iterator
    {
        $fileInfos = self::findFilesInDirectory($directory, $suffix);
        foreach ($fileInfos as $fileInfo) {
            (yield [$fileInfo->getRealPath()]);
        }
    }
    /**
     * @return SplFileInfo[]
     */
    private static function findFilesInDirectory(string $directory, string $suffix) : array
    {
        $finder = (new Finder())->in($directory)->files()->name($suffix);
        $fileInfos = \iterator_to_array($finder);
        return \array_values($fileInfos);
    }
}
