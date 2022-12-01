<?php

declare (strict_types=1);
namespace Rector\Caching\ValueObject\Storage;

use FilesystemIterator;
use RectorPrefix202212\Nette\Utils\FileSystem;
use RectorPrefix202212\Nette\Utils\Random;
use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Caching\ValueObject\CacheFilePaths;
use Rector\Caching\ValueObject\CacheItem;
use Rector\Core\Exception\Cache\CachingException;
/**
 * Inspired by https://github.com/phpstan/phpstan-src/blob/1e7ceae933f07e5a250b61ed94799e6c2ea8daa2/src/Cache/FileCacheStorage.php
 * @see \Rector\Tests\Caching\ValueObject\Storage\FileCacheStorageTest
 */
final class FileCacheStorage implements CacheStorageInterface
{
    /**
     * @var string
     */
    private $directory;
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;
    public function __construct(string $directory, \RectorPrefix202212\Symfony\Component\Filesystem\Filesystem $filesystem)
    {
        $this->directory = $directory;
        $this->filesystem = $filesystem;
    }
    public function load(string $key, string $variableKey)
    {
        return (function (string $key, string $variableKey) {
            $cacheFilePaths = $this->getCacheFilePaths($key);
            $filePath = $cacheFilePaths->getFilePath();
            if (!\is_file($filePath)) {
                return null;
            }
            $cacheItem = (require $filePath);
            if (!$cacheItem instanceof CacheItem) {
                return null;
            }
            if (!$cacheItem->isVariableKeyValid($variableKey)) {
                return null;
            }
            return $cacheItem->getData();
        })($key, $variableKey);
    }
    /**
     * @param mixed $data
     */
    public function save(string $key, string $variableKey, $data) : void
    {
        $cacheFilePaths = $this->getCacheFilePaths($key);
        $this->filesystem->mkdir($cacheFilePaths->getFirstDirectory());
        $this->filesystem->mkdir($cacheFilePaths->getSecondDirectory());
        $path = $cacheFilePaths->getFilePath();
        $tmpPath = \sprintf('%s/%s.tmp', $this->directory, Random::generate());
        $errorBefore = \error_get_last();
        $exported = @\var_export(new CacheItem($variableKey, $data), \true);
        $errorAfter = \error_get_last();
        if ($errorAfter !== null && $errorBefore !== $errorAfter) {
            throw new CachingException(\sprintf('Error occurred while saving item %s (%s) to cache: %s', $key, $variableKey, $errorAfter['message']));
        }
        // for performance reasons we don't use SmartFileSystem
        FileSystem::write($tmpPath, \sprintf("<?php declare(strict_types = 1);\n\nreturn %s;", $exported));
        $renameSuccess = @\rename($tmpPath, $path);
        if ($renameSuccess) {
            return;
        }
        @\unlink($tmpPath);
        if (\DIRECTORY_SEPARATOR === '/' || !\file_exists($path)) {
            throw new CachingException(\sprintf('Could not write data to cache file %s.', $path));
        }
    }
    public function clean(string $key) : void
    {
        $cacheFilePaths = $this->getCacheFilePaths($key);
        $this->processRemoveCacheFilePath($cacheFilePaths);
        $this->processRemoveEmptyDirectory($cacheFilePaths->getSecondDirectory());
        $this->processRemoveEmptyDirectory($cacheFilePaths->getFirstDirectory());
    }
    public function clear() : void
    {
        $this->filesystem->remove($this->directory);
    }
    private function processRemoveCacheFilePath(CacheFilePaths $cacheFilePaths) : void
    {
        $filePath = $cacheFilePaths->getFilePath();
        if (!$this->filesystem->exists($filePath)) {
            return;
        }
        $this->filesystem->remove($filePath);
    }
    private function processRemoveEmptyDirectory(string $directory) : void
    {
        if (!$this->filesystem->exists($directory)) {
            return;
        }
        if ($this->isNotEmptyDirectory($directory)) {
            return;
        }
        $this->filesystem->remove($directory);
    }
    private function isNotEmptyDirectory(string $directory) : bool
    {
        // FilesystemIterator will initially point to the first file in the folder - if there are no files in the folder, valid() will return false
        $filesystemIterator = new FilesystemIterator($directory);
        return $filesystemIterator->valid();
    }
    private function getCacheFilePaths(string $key) : CacheFilePaths
    {
        $keyHash = \sha1($key);
        $firstDirectory = \sprintf('%s/%s', $this->directory, \substr($keyHash, 0, 2));
        $secondDirectory = \sprintf('%s/%s', $firstDirectory, \substr($keyHash, 2, 2));
        $filePath = \sprintf('%s/%s.php', $secondDirectory, $keyHash);
        return new CacheFilePaths($firstDirectory, $secondDirectory, $filePath);
    }
}
