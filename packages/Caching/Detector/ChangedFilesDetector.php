<?php

declare(strict_types=1);

namespace Rector\Caching\Detector;

use Nette\Utils\Strings;
use Rector\Caching\Cache;
use Rector\Caching\Config\FileHashComputer;
use Rector\Caching\Enum\CacheKey;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspired by https://github.com/symplify/symplify/pull/90/files#diff-72041b2e1029a08930e13d79d298ef11
 *
 * @see \Rector\Tests\Caching\Detector\ChangedFilesDetectorTest
 */
final class ChangedFilesDetector
{
    public function __construct(
        private FileHashComputer $fileHashComputer,
        private Cache $cache
    ) {
    }

    /**
     * @param string[] $dependentFiles
     */
    public function addFileWithDependencies(SmartFileInfo $smartFileInfo, array $dependentFiles): void
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);
        $hash = $this->hashFile($smartFileInfo);

        $this->cache->save($fileInfoCacheKey, CacheKey::FILE_HASH_KEY, $hash);
        $this->cache->save($fileInfoCacheKey . '_files', CacheKey::DEPENDENT_FILES_KEY, $dependentFiles);
    }

    public function hasFileChanged(SmartFileInfo $smartFileInfo): bool
    {
        $currentFileHash = $this->hashFile($smartFileInfo);

        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);

        $cachedValue = $this->cache->load($fileInfoCacheKey, CacheKey::FILE_HASH_KEY);
        return $currentFileHash !== $cachedValue;
    }

    public function invalidateFile(SmartFileInfo $smartFileInfo): void
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);
        $this->cache->clean($fileInfoCacheKey);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getDependentFileInfos(SmartFileInfo $fileInfo): array
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($fileInfo);

        $cacheValue = $this->cache->load($fileInfoCacheKey . '_files', CacheKey::DEPENDENT_FILES_KEY);
        if ($cacheValue === null) {
            return [];
        }

        $dependentFileInfos = [];

        $dependentFiles = $cacheValue;
        foreach ($dependentFiles as $dependentFile) {
            if (! file_exists($dependentFile)) {
                continue;
            }

            $dependentFileInfos[] = new SmartFileInfo($dependentFile);
        }

        return $dependentFileInfos;
    }

    /**
     * @api
     */
    public function setFirstResolvedConfigFileInfo(SmartFileInfo $fileInfo): void
    {
        // the first config is core to all → if it was changed, just invalidate it
        $configHash = $this->fileHashComputer->compute($fileInfo);
        $this->storeConfigurationDataHash($fileInfo, $configHash);
    }

    private function getFileInfoCacheKey(SmartFileInfo $smartFileInfo): string
    {
        return sha1($smartFileInfo->getRealPath());
    }

    private function hashFile(SmartFileInfo $smartFileInfo): string
    {
        return (string) sha1_file($smartFileInfo->getRealPath());
    }

    private function storeConfigurationDataHash(SmartFileInfo $fileInfo, string $configurationHash): void
    {
        $key = CacheKey::CONFIGURATION_HASH_KEY . '_' . Strings::webalize($fileInfo->getRealPath());
        $this->invalidateCacheIfConfigurationChanged($key, $configurationHash);

        $this->cache->save($key, CacheKey::CONFIGURATION_HASH_KEY, $configurationHash);
    }

    private function invalidateCacheIfConfigurationChanged(string $key, string $configurationHash): void
    {
        $oldCachedValue = $this->cache->load($key, CacheKey::CONFIGURATION_HASH_KEY);
        if ($oldCachedValue === $configurationHash) {
            return;
        }

        // should be unique per getcwd()
        $this->clear();
    }
}
