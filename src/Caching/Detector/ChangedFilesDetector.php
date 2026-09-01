<?php

declare (strict_types=1);
namespace Rector\Caching\Detector;

use Rector\Caching\Cache;
use Rector\Caching\Config\FileHashComputer;
use Rector\Caching\Enum\CacheKey;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Util\FileHasher;
/**
 * Inspired by https://github.com/symplify/symplify/pull/90/files#diff-72041b2e1029a08930e13d79d298ef11
 *
 * @see \Rector\Tests\Caching\Detector\ChangedFilesDetectorTest
 */
final class ChangedFilesDetector
{
    /**
     * @readonly
     */
    private FileHashComputer $fileHashComputer;
    /**
     * @readonly
     */
    private Cache $cache;
    /**
     * @readonly
     */
    private FileHasher $fileHasher;
    /**
     * @var array<string, true>
     */
    private array $cacheableFiles = [];
    // scopes the per-file cache key to the active --only / --only-suffix / --filter selection (empty = full run)
    private string $scopeSuffix = '';
    public function __construct(FileHashComputer $fileHashComputer, Cache $cache, FileHasher $fileHasher)
    {
        $this->fileHashComputer = $fileHashComputer;
        $this->cache = $cache;
        $this->fileHasher = $fileHasher;
    }
    /**
     * @param string[] $filters
     */
    public function setActiveScope(?string $onlyRule, ?string $onlySuffix, array $filters = []): void
    {
        // each selection gets its own cache key, so --only and full runs coexist without clearing or poisoning
        $this->scopeSuffix = $onlyRule === null && $onlySuffix === null && $filters === [] ? '' : '|only:' . ($onlyRule ?? '') . '|suffix:' . ($onlySuffix ?? '') . '|filter:' . implode(',', $filters);
    }
    public function cacheFile(string $filePath): void
    {
        $filePathCacheKey = $this->getFilePathCacheKey($filePath);
        if (!isset($this->cacheableFiles[$filePathCacheKey])) {
            return;
        }
        $hash = $this->hashFile($filePath);
        $this->cache->save($filePathCacheKey, CacheKey::FILE_HASH_KEY, $hash);
    }
    public function addCacheableFile(string $filePath): void
    {
        $filePathCacheKey = $this->getFilePathCacheKey($filePath);
        $this->cacheableFiles[$filePathCacheKey] = \true;
    }
    public function hasFileChanged(string $filePath): bool
    {
        $cachedValue = $this->cache->load($this->getFilePathCacheKey($filePath), CacheKey::FILE_HASH_KEY);
        // a scoped (--only) run reuses the full-run cache: a file left clean by all rules stays
        // clean under a single rule too, and the content is still compared below
        if ($cachedValue === null && $this->scopeSuffix !== '') {
            $unscopedCacheKey = $this->fileHasher->hash($this->resolvePath($filePath));
            $cachedValue = $this->cache->load($unscopedCacheKey, CacheKey::FILE_HASH_KEY);
        }
        if ($cachedValue !== null) {
            $currentFileHash = $this->hashFile($filePath);
            return $currentFileHash !== $cachedValue;
        }
        // we don't have a value to compare against. Be defensive and assume its changed
        return \true;
    }
    public function invalidateFile(string $filePath): void
    {
        $fileInfoCacheKey = $this->getFilePathCacheKey($filePath);
        $this->cache->clean($fileInfoCacheKey);
        unset($this->cacheableFiles[$fileInfoCacheKey]);
    }
    public function clear(): void
    {
        $this->cache->clear();
    }
    /**
     * @api
     */
    public function setFirstResolvedConfigFileInfo(string $filePath): void
    {
        // the first config is core to all → if it was changed, just invalidate it
        $configurationSnapshot = $this->createConfigurationSnapshot($filePath);
        $this->storeConfigurationDataHash($filePath, $configurationSnapshot);
    }
    private function resolvePath(string $filePath): string
    {
        $realPath = realpath($filePath);
        if ($realPath === \false) {
            return $filePath;
        }
        return $realPath;
    }
    private function getFilePathCacheKey(string $filePath): string
    {
        return $this->fileHasher->hash($this->resolvePath($filePath) . $this->scopeSuffix);
    }
    private function hashFile(string $filePath): string
    {
        return $this->fileHasher->hashFiles([$this->resolvePath($filePath)]);
    }
    /**
     * @return array{hash: string, rules: string[], sets: string[], skip: string[]}
     */
    private function createConfigurationSnapshot(string $filePath): array
    {
        $directionalParameters = SimpleParameterProvider::provideCacheDirectionalParameters();
        return ['hash' => $this->fileHashComputer->compute($filePath), 'rules' => $this->hashEach($directionalParameters['rules']), 'sets' => $this->hashEach($directionalParameters['sets']), 'skip' => $this->hashEach($directionalParameters['skip'])];
    }
    /**
     * @param array{hash: string, rules: string[], sets: string[], skip: string[]} $configurationSnapshot
     */
    private function storeConfigurationDataHash(string $filePath, array $configurationSnapshot): void
    {
        $key = CacheKey::CONFIGURATION_HASH_KEY . '_' . $this->getFilePathCacheKey($filePath);
        $this->invalidateCacheIfConfigurationChanged($key, $configurationSnapshot);
        $this->cache->save($key, CacheKey::CONFIGURATION_HASH_KEY, $configurationSnapshot);
    }
    /**
     * @param array{hash: string, rules: string[], sets: string[], skip: string[]} $configurationSnapshot
     */
    private function invalidateCacheIfConfigurationChanged(string $key, array $configurationSnapshot): void
    {
        $oldCachedValue = $this->cache->load($key, CacheKey::CONFIGURATION_HASH_KEY);
        // first run, nothing to compare against
        if ($oldCachedValue === null) {
            return;
        }
        // legacy string format from an older Rector version → be safe and reset once
        if (!is_array($oldCachedValue)) {
            $this->clear();
            return;
        }
        if ($this->shouldInvalidateCache($oldCachedValue, $configurationSnapshot)) {
            // should be unique per getcwd()
            $this->clear();
        }
    }
    /**
     * @param array<string, mixed> $oldSnapshot
     * @param array{hash: string, rules: string[], sets: string[], skip: string[]} $newSnapshot
     */
    private function shouldInvalidateCache(array $oldSnapshot, array $newSnapshot): bool
    {
        // an output-affecting parameter changed (php version, import names, indent, configured rule value, ...)
        if (($oldSnapshot['hash'] ?? null) !== $newSnapshot['hash']) {
            return \true;
        }
        // a rule or set was added → files clean so far may now be refactored
        if ($this->hasAddedEntry($oldSnapshot['rules'] ?? [], $newSnapshot['rules'])) {
            return \true;
        }
        if ($this->hasAddedEntry($oldSnapshot['sets'] ?? [], $newSnapshot['sets'])) {
            return \true;
        }
        // a skip was removed → previously skipped transformations may now apply
        return $this->hasAddedEntry($newSnapshot['skip'], $oldSnapshot['skip'] ?? []);
    }
    /**
     * Is there any entry present in $comparedEntries but missing from $baseEntries?
     * A non-array on either side means an unexpected shape, treated as changed to stay safe.
     * @param mixed $baseEntries
     * @param mixed $comparedEntries
     */
    private function hasAddedEntry($baseEntries, $comparedEntries): bool
    {
        if (!is_array($baseEntries) || !is_array($comparedEntries)) {
            return \true;
        }
        $found = \false;
        foreach ($comparedEntries as $comparedEntry) {
            if (!in_array($comparedEntry, $baseEntries, \true)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    /**
     * @param mixed[] $values
     * @return string[]
     */
    private function hashEach(array $values): array
    {
        return array_map(static fn($value): string => sha1(serialize($value)), array_values($values));
    }
}
