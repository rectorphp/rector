<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\FileSystem;

use RectorPrefix202609\Entropy\Attributes\RelatedTest;
use RectorPrefix202609\Entropy\Tests\FileSystem\FileFinder\FileFinderTest;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
final class FileFinder
{
    /**
     * @api used in tests
     * @return string[]
     */
    public static function findPhpFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            if ($fileInfo->getExtension() !== 'php') {
                continue;
            }
            if (self::isNonService($fileInfo)) {
                continue;
            }
            $files[] = $fileInfo->getPathname();
        }
        return $files;
    }
    private static function isNonService(SplFileInfo $fileInfo): bool
    {
        if (strpos($fileInfo->getPathname(), '/ValueObject/') !== \false) {
            return \true;
        }
        return strpos($fileInfo->getPathname(), '/Enum/') !== \false;
    }
}
