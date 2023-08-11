<?php

declare (strict_types=1);
namespace Rector\Caching\Detector;

use Rector\Caching\Cache;
use Rector\Caching\Config\FileHashComputer;
use Rector\Caching\Enum\CacheKey;
use Rector\Core\Util\FileHasher;
/**
 * Inspired by https://github.com/symplify/symplify/pull/90/files#diff-72041b2e1029a08930e13d79d298ef11
 *
 * @see \Rector\Tests\Caching\Detector\ChangedFilesDetectorTest
 */
final class ChangedFilesDetector
{
    /**
     * @readonly
     * @var \Rector\Caching\Config\FileHashComputer
     */
    private $fileHashComputer;
    /**
     * @readonly
     * @var \Rector\Caching\Cache
     */
    private $cache;
    /**
     * @readonly
     * @var \Rector\Core\Util\FileHasher
     */
    private $fileHasher;
    /**
     * @var array<string, true>
     */
    private $cachableFiles = [];
    public function __construct(FileHashComputer $fileHashComputer, Cache $cache, FileHasher $fileHasher)
    {
        $this->fileHashComputer = $fileHashComputer;
        $this->cache = $cache;
        $this->fileHasher = $fileHasher;
    }
    public function cacheFile(string $filePath) : void
    {
        $filePathCacheKey = $this->getFilePathCacheKey($filePath);
        if (!isset($this->cachableFiles[$filePathCacheKey])) {
            return;
        }
        $hash = $this->hashFile($filePath);
        $this->cache->save($filePathCacheKey, CacheKey::FILE_HASH_KEY, $hash);
    }
    public function addCachableFile(string $filePath) : void
    {
        $filePathCacheKey = $this->getFilePathCacheKey($filePath);
        $this->cachableFiles[$filePathCacheKey] = \true;
    }
    public function hasFileChanged(string $filePath) : bool
    {
        $fileInfoCacheKey = $this->getFilePathCacheKey($filePath);
        $cachedValue = $this->cache->load($fileInfoCacheKey, CacheKey::FILE_HASH_KEY);
        if ($cachedValue !== null) {
            $currentFileHash = $this->hashFile($filePath);
            return $currentFileHash !== $cachedValue;
        }
        // we don't have a value to compare against. Be defensive and assume its changed
        return \true;
    }
    public function invalidateFile(string $filePath) : void
    {
        $fileInfoCacheKey = $this->getFilePathCacheKey($filePath);
        $this->cache->clean($fileInfoCacheKey);
        unset($this->cachableFiles[$fileInfoCacheKey]);
    }
    public function clear() : void
    {
        $this->cache->clear();
    }
    /**
     * @api
     */
    public function setFirstResolvedConfigFileInfo(string $filePath) : void
    {
        // the first config is core to all → if it was changed, just invalidate it
        $configHash = $this->fileHashComputer->compute($filePath);
        $this->storeConfigurationDataHash($filePath, $configHash);
    }
    private function resolvePath(string $filePath) : string
    {
        $realPath = \realpath($filePath);
        if ($realPath === \false) {
            return $filePath;
        }
        return $realPath;
    }
    private function getFilePathCacheKey(string $filePath) : string
    {
        return $this->fileHasher->hash($this->resolvePath($filePath));
    }
    private function hashFile(string $filePath) : string
    {
        return $this->fileHasher->hashFiles([$this->resolvePath($filePath)]);
    }
    private function storeConfigurationDataHash(string $filePath, string $configurationHash) : void
    {
        $key = CacheKey::CONFIGURATION_HASH_KEY . '_' . $this->getFilePathCacheKey($filePath);
        $this->invalidateCacheIfConfigurationChanged($key, $configurationHash);
        $this->cache->save($key, CacheKey::CONFIGURATION_HASH_KEY, $configurationHash);
    }
    private function invalidateCacheIfConfigurationChanged(string $key, string $configurationHash) : void
    {
        $oldCachedValue = $this->cache->load($key, CacheKey::CONFIGURATION_HASH_KEY);
        if ($oldCachedValue === null) {
            return;
        }
        if ($oldCachedValue === $configurationHash) {
            return;
        }
        // should be unique per getcwd()
        $this->clear();
    }
}
