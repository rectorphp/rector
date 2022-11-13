<?php

declare (strict_types=1);
namespace Rector\Caching\Detector;

use RectorPrefix202211\Nette\Utils\Strings;
use Rector\Caching\Cache;
use Rector\Caching\Config\FileHashComputer;
use Rector\Caching\Enum\CacheKey;
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
    public function __construct(FileHashComputer $fileHashComputer, Cache $cache)
    {
        $this->fileHashComputer = $fileHashComputer;
        $this->cache = $cache;
    }
    /**
     * @param string[] $dependentFiles
     */
    public function addFileWithDependencies(string $filePath, array $dependentFiles) : void
    {
        $filePathCacheKey = $this->getFilePathCacheKey($filePath);
        $hash = $this->hashFile($filePath);
        $this->cache->save($filePathCacheKey, CacheKey::FILE_HASH_KEY, $hash);
        $this->cache->save($filePathCacheKey . '_files', CacheKey::DEPENDENT_FILES_KEY, $dependentFiles);
    }
    public function hasFileChanged(string $filePath) : bool
    {
        $currentFileHash = $this->hashFile($filePath);
        $fileInfoCacheKey = $this->getFilePathCacheKey($filePath);
        $cachedValue = $this->cache->load($fileInfoCacheKey, CacheKey::FILE_HASH_KEY);
        return $currentFileHash !== $cachedValue;
    }
    public function invalidateFile(string $filePath) : void
    {
        $fileInfoCacheKey = $this->getFilePathCacheKey($filePath);
        $this->cache->clean($fileInfoCacheKey);
    }
    public function clear() : void
    {
        $this->cache->clear();
    }
    /**
     * @return string[]
     */
    public function getDependentFilePaths(string $filePath) : array
    {
        $fileInfoCacheKey = $this->getFilePathCacheKey($filePath);
        $cacheValue = $this->cache->load($fileInfoCacheKey . '_files', CacheKey::DEPENDENT_FILES_KEY);
        if ($cacheValue === null) {
            return [];
        }
        $existingDependentFiles = [];
        $dependentFiles = $cacheValue;
        foreach ($dependentFiles as $dependentFile) {
            if (!\file_exists($dependentFile)) {
                continue;
            }
            $existingDependentFiles[] = $dependentFile;
        }
        return $existingDependentFiles;
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
        return \sha1($this->resolvePath($filePath));
    }
    private function hashFile(string $filePath) : string
    {
        return (string) \sha1_file($this->resolvePath($filePath));
    }
    private function storeConfigurationDataHash(string $filePath, string $configurationHash) : void
    {
        $key = CacheKey::CONFIGURATION_HASH_KEY . '_' . Strings::webalize($filePath);
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
