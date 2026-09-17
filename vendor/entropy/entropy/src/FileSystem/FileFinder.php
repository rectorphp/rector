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
     * Generic recursive finder. Returns files across all directories, sorted by path,
     * each aware of its path relative to the directory it was found in.
     *
     * @api
     * @param string[] $directories
     * @param (callable(FileInfo): bool)|null $filter
     * @return FileInfo[]
     */
    public static function find(array $directories, ?callable $filter = null): array
    {
        $fileInfos = [];
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }
            $baseDirectory = str_replace('\\', '/', (string) realpath($directory));
            $recursiveDirectoryIterator = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
            $recursiveIteratorIterator = new RecursiveIteratorIterator($recursiveDirectoryIterator);
            foreach ($recursiveIteratorIterator as $splFileInfo) {
                if (!$splFileInfo instanceof SplFileInfo) {
                    continue;
                }
                if (!$splFileInfo->isFile()) {
                    continue;
                }
                $filePath = (string) $splFileInfo->getRealPath();
                $relativePathname = self::resolveRelativePathname($filePath, $baseDirectory);
                $relativeDirectory = dirname($relativePathname);
                $fileInfo = new FileInfo($filePath, $relativeDirectory === '.' ? '' : $relativeDirectory, $relativePathname);
                if ($filter !== null && !$filter($fileInfo)) {
                    continue;
                }
                $fileInfos[$filePath] = $fileInfo;
            }
        }
        ksort($fileInfos);
        return array_values($fileInfos);
    }
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
    private static function resolveRelativePathname(string $filePath, string $baseDirectory): string
    {
        $filePath = str_replace('\\', '/', $filePath);
        if ($baseDirectory !== '' && strncmp($filePath, $baseDirectory . '/', strlen($baseDirectory . '/')) === 0) {
            return (string) substr($filePath, strlen($baseDirectory) + 1);
        }
        return $filePath;
    }
    private static function isNonService(SplFileInfo $fileInfo): bool
    {
        if (strpos($fileInfo->getPathname(), '/ValueObject/') !== \false) {
            return \true;
        }
        return strpos($fileInfo->getPathname(), '/Enum/') !== \false;
    }
}
