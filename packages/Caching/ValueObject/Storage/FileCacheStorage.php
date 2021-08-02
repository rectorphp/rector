<?php

declare(strict_types=1);

namespace Rector\Caching\ValueObject\Storage;

use Nette\Utils\Random;
use PHPStan\File\FileWriter;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Caching\ValueObject\CacheFilePaths;
use Rector\Caching\ValueObject\CacheItem;
use Symplify\EasyCodingStandard\Caching\Exception\CachingException;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * Inspired by https://github.com/phpstan/phpstan-src/blob/1e7ceae933f07e5a250b61ed94799e6c2ea8daa2/src/Cache/FileCacheStorage.php
 */
final class FileCacheStorage implements CacheStorageInterface
{
    public function __construct(
        private string $directory,
        private SmartFileSystem $smartFileSystem
    ) {
    }

    public function load(string $key, string $variableKey)
    {
        return (function (string $key, string $variableKey) {
            $cacheFilePaths = $this->getCacheFilePaths($key);

            $filePath = $cacheFilePaths->getFilePath();
            if (! \is_file($filePath)) {
                return null;
            }
            $cacheItem = (require $filePath);
            if (! $cacheItem instanceof CacheItem) {
                return null;
            }
            if (! $cacheItem->isVariableKeyValid($variableKey)) {
                return null;
            }
            return $cacheItem->getData();
        })($key, $variableKey);
    }

    public function save(string $key, string $variableKey, $data): void
    {
        $cacheFilePaths = $this->getCacheFilePaths($key);
        $this->smartFileSystem->mkdir($cacheFilePaths->getFirstDirectory());
        $this->smartFileSystem->mkdir($cacheFilePaths->getSecondDirectory());
        $path = $cacheFilePaths->getFilePath();

        $tmpPath = \sprintf('%s/%s.tmp', $this->directory, Random::generate());
        $errorBefore = \error_get_last();
        $exported = @\var_export(new CacheItem($variableKey, $data), true);
        $errorAfter = \error_get_last();
        if ($errorAfter !== null && $errorBefore !== $errorAfter) {
            throw new CachingException(\sprintf(
                'Error occurred while saving item %s (%s) to cache: %s',
                $key,
                $variableKey,
                $errorAfter['message']
            ));
        }
        // for performance reasons we don't use SmartFileSystem
        FileWriter::write($tmpPath, \sprintf("<?php declare(strict_types = 1);\n\nreturn %s;", $exported));
        $renameSuccess = @\rename($tmpPath, $path);
        if ($renameSuccess) {
            return;
        }
        @\unlink($tmpPath);
        if (\DIRECTORY_SEPARATOR === '/' || ! \file_exists($path)) {
            throw new CachingException(\sprintf('Could not write data to cache file %s.', $path));
        }
    }

    public function clean(string $key): void
    {
        $cacheFilePaths = $this->getCacheFilePaths($key);

        $this->smartFileSystem->remove([
            $cacheFilePaths->getFirstDirectory(),
            $cacheFilePaths->getSecondDirectory(),
            $cacheFilePaths->getFilePath(),
        ]);
    }

    public function clear(): void
    {
        $this->smartFileSystem->remove($this->directory);
    }

    private function getCacheFilePaths(string $key): CacheFilePaths
    {
        $keyHash = sha1($key);
        $firstDirectory = sprintf('%s/%s', $this->directory, substr($keyHash, 0, 2));
        $secondDirectory = sprintf('%s/%s', $firstDirectory, substr($keyHash, 2, 2));
        $filePath = sprintf('%s/%s.php', $secondDirectory, $keyHash);

        return new CacheFilePaths($firstDirectory, $secondDirectory, $filePath);
    }
}
