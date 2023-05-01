<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use Iterator;
use RectorPrefix202305\Symfony\Component\Finder\Finder;
use RectorPrefix202305\Symfony\Component\Finder\SplFileInfo;
final class FixtureFileFinder
{
    /**
     * @return Iterator<array<int, string>>
     */
    public static function yieldDirectory(string $directory, string $suffix = '*.php.inc') : Iterator
    {
        $finder = (new Finder())->in($directory)->files()->name($suffix);
        $fileInfos = \iterator_to_array($finder);
        foreach ($fileInfos as $fileInfo) {
            (yield [$fileInfo->getRealPath()]);
        }
    }
}
