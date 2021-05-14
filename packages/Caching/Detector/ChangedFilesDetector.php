<?php

declare (strict_types=1);
namespace Rector\Caching\Detector;

use RectorPrefix20210514\Nette\Caching\Cache;
use RectorPrefix20210514\Nette\Utils\Strings;
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
    /**
     * @var \Rector\Caching\Config\FileHashComputer
     */
    private $fileHashComputer;
    /**
     * @var \Nette\Caching\Cache
     */
    private $cache;
    public function __construct(\Rector\Caching\Config\FileHashComputer $fileHashComputer, \RectorPrefix20210514\Nette\Caching\Cache $cache)
    {
        $this->fileHashComputer = $fileHashComputer;
        $this->cache = $cache;
    }
    /**
     * @param string[] $dependentFiles
     */
    public function addFileWithDependencies(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, array $dependentFiles) : void
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);
        $hash = $this->hashFile($smartFileInfo);
        $this->cache->save($fileInfoCacheKey, $hash);
        $this->cache->save($fileInfoCacheKey . '_files', $dependentFiles);
    }
    public function hasFileChanged(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : bool
    {
        $currentFileHash = $this->hashFile($smartFileInfo);
        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);
        $cachedValue = $this->cache->load($fileInfoCacheKey);
        return $currentFileHash !== $cachedValue;
    }
    public function invalidateFile(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : void
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);
        $this->cache->remove($fileInfoCacheKey);
    }
    public function clear() : void
    {
        $this->cache->clean([\RectorPrefix20210514\Nette\Caching\Cache::ALL => \true]);
    }
    /**
     * @return SmartFileInfo[]
     */
    public function getDependentFileInfos(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : array
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($fileInfo);
        $cacheValue = $this->cache->load($fileInfoCacheKey . '_files');
        if ($cacheValue === null) {
            return [];
        }
        $dependentFileInfos = [];
        $dependentFiles = $cacheValue;
        foreach ($dependentFiles as $dependentFile) {
            if (!\file_exists($dependentFile)) {
                continue;
            }
            $dependentFileInfos[] = new \Symplify\SmartFileSystem\SmartFileInfo($dependentFile);
        }
        return $dependentFileInfos;
    }
    /**
     * @api
     */
    public function setFirstResolvedConfigFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : void
    {
        // the first config is core to all → if it was changed, just invalidate it
        $configHash = $this->fileHashComputer->compute($fileInfo);
        $this->storeConfigurationDataHash($fileInfo, $configHash);
    }
    private function getFileInfoCacheKey(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : string
    {
        return \sha1($smartFileInfo->getRealPath());
    }
    private function hashFile(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : string
    {
        return (string) \sha1_file($smartFileInfo->getRealPath());
    }
    private function storeConfigurationDataHash(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo, string $configurationHash) : void
    {
        $key = self::CONFIGURATION_HASH_KEY . '_' . \RectorPrefix20210514\Nette\Utils\Strings::webalize($fileInfo->getRealPath());
        $this->invalidateCacheIfConfigurationChanged($key, $configurationHash);
        $this->cache->save($key, $configurationHash);
    }
    private function invalidateCacheIfConfigurationChanged(string $key, string $configurationHash) : void
    {
        $oldCachedValue = $this->cache->load($key);
        if ($oldCachedValue === $configurationHash) {
            return;
        }
        // should be unique per getcwd()
        $this->clear();
    }
}
