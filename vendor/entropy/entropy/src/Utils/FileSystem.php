<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Utils;

use RectorPrefix202608\Entropy\FileSystem\Exception\FileSystemException;
use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * @api public api to use
 */
final class FileSystem
{
    public static function read(string $filePath): string
    {
        Assert::fileExists($filePath);
        $fileContents = file_get_contents($filePath);
        Assert::notFalse($fileContents, sprintf('Failed to read the "%s" file', $filePath));
        return $fileContents;
    }
    public static function write(string $filePath, string $contents): void
    {
        $result = file_put_contents($filePath, $contents);
        Assert::notFalse($result, sprintf('Failed to write to the "%s" file. Contents: "%s"', $filePath, $contents));
    }
    public static function delete(string $fileOrDirectory): void
    {
        if (is_dir($fileOrDirectory)) {
            self::deleteDirectory($fileOrDirectory);
        } elseif (is_file($fileOrDirectory)) {
            unlink($fileOrDirectory);
        }
    }
    public static function ensureDirectoryExists(string $directoryPath): void
    {
        if (is_dir($directoryPath)) {
            return;
        }
        mkdir($directoryPath, 0777, \true);
    }
    /**
     * @return array<string, mixed>
     */
    public static function loadFileToJson(string $filePath): array
    {
        $fileContents = self::read($filePath);
        return Json::decode($fileContents);
    }
    /**
     * @param array<string, mixed> $json
     */
    public static function saveJsonToFile(array $json, string $targetFilePath): void
    {
        $jsonContents = Json::encode($json);
        file_put_contents($targetFilePath, $jsonContents);
    }
    private static function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        // safety: never allow root or empty paths
        $realPath = realpath($directory);
        if (in_array($realPath, [\false, '/', ''], \true)) {
            throw new FileSystemException(sprintf('Refusing to delete unsafe directory: "%s"', $directory));
        }
        foreach (scandir($realPath) as $item) {
            if (in_array($item, ['.', '..'], \true)) {
                continue;
            }
            $fullPath = $realPath . \DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath) && !is_link($fullPath)) {
                self::deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }
        rmdir($realPath);
    }
}
