<?php

declare(strict_types=1);

namespace Rector\Core\Testing;

use Iterator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class StaticFixtureProvider
{
    public static function yieldFilesFromDirectory(string $directory, string $suffix): Iterator
    {
        $fileInfos = self::findFilesInDirectory($directory, $suffix);

        foreach ($fileInfos as $fileInfo) {
            yield [$fileInfo->getPathName()];
        }
    }

    /**
     * @return string[]
     */
    public static function findFilesFromDirectory(string $directory, string $suffix): array
    {
        $fileInfos = self::findFilesInDirectory($directory, $suffix);

        $filePaths = [];
        foreach ($fileInfos as $fileInfo) {
            $filePaths[] = $fileInfo->getPathName();
        }

        return $filePaths;
    }

    /**
     * @return SplFileInfo[]
     */
    private static function findFilesInDirectory(string $directory, string $suffix): array
    {
        $finder = Finder::create()->in($directory)->files()
            ->name($suffix);

        $fileInfos = iterator_to_array($finder);

        return array_values($fileInfos);
    }
}
