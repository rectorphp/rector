<?php

declare(strict_types=1);

namespace Rector\Caching\Detector;

use Nette\Caching\Cache;
use Nette\Utils\Strings;
use Rector\Caching\Config\FileHashComputer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspired by https://github.com/symplify/symplify/pull/90/files#diff-72041b2e1029a08930e13d79d298ef11
 * @see \Rector\Caching\Tests\Detector\ChangedFilesDetectorTest
 */
final class ChangedFilesDetector
{
    /**
     * @var string
     */
    private const CONFIGURATION_HASH_KEY = 'configuration_hash';

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

        $this->cache->save($fileInfoCacheKey, $hash);
        $this->cache->save($fileInfoCacheKey . '_files', $dependentFiles);
    }

    public function hasFileChanged(SmartFileInfo $smartFileInfo): bool
    {
        $currentFileHash = $this->hashFile($smartFileInfo);

        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);

        $cachedValue = $this->cache->load($fileInfoCacheKey);
        return $currentFileHash !== $cachedValue;
    }

    public function invalidateFile(SmartFileInfo $smartFileInfo): void
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);
        $this->cache->remove($fileInfoCacheKey);
    }

    public function clear(): void
    {
        $this->cache->clean([
            Cache::ALL => true,
        ]);
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getDependentFileInfos(SmartFileInfo $fileInfo): array
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($fileInfo);

        $cacheValue = $this->cache->load($fileInfoCacheKey . '_files');
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
        $key = self::CONFIGURATION_HASH_KEY . '_' . Strings::webalize($fileInfo->getRealPath());
        $this->invalidateCacheIfConfigurationChanged($key, $configurationHash);

        $this->cache->save($key, $configurationHash);
    }

    private function invalidateCacheIfConfigurationChanged(string $key, string $configurationHash): void
    {
        $oldCachedValue = $this->cache->load($key);
        if ($oldCachedValue === $configurationHash) {
            return;
        }

        // should be unique per getcwd()
        $this->clear();
    }
}
