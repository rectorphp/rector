<?php

declare (strict_types=1);
namespace Rector\Testing\Fixture;

use Iterator;
use RectorPrefix202609\Symfony\Component\Finder\Finder;
final class FixtureFileFinder
{
    /**
     * @api used in tests
     * @return Iterator<string, array<int, string>>
     */
    public static function yieldDirectory(string $directory, string $suffix = '*.php.inc'): Iterator
    {
        $finder = (new Finder())->in($directory)->files()->name($suffix)->sortByName();
        foreach ($finder as $fileInfo) {
            // key the data set by fixture path, so a failure prints the exact
            // clickable ".php.inc" file instead of an anonymous "data set #N"
            yield $fileInfo->getRealPath() => [$fileInfo->getRealPath()];
        }
    }
}
