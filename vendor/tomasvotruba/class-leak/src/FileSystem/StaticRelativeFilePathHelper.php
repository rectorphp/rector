<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\ClassLeak\FileSystem;

/**
 * @see \TomasVotruba\ClassLeak\Tests\FileSystem\StaticRelativeFilePathHelperTest
 */
final class StaticRelativeFilePathHelper
{
    public static function resolveFromCwd(string $filePath): string
    {
        // make path relative with native PHP
        $relativeFilePath = (string) realpath($filePath);
        return str_replace(getcwd() . \DIRECTORY_SEPARATOR, '', $relativeFilePath);
    }
}
