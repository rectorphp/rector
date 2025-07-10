<?php

declare (strict_types=1);
namespace Rector\Caching\Detector;

use Rector\Caching\Cache;
use Rector\Caching\Enum\CacheKey;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Util\FileHasher;
final class KaizenRulesDetector
{
    /**
     * @readonly
     */
    private Cache $cache;
    /**
     * @readonly
     */
    private FileHasher $fileHasher;
    public function __construct(Cache $cache, FileHasher $fileHasher)
    {
        $this->cache = $cache;
        $this->fileHasher = $fileHasher;
    }
    public function addRule(string $rectorClass) : void
    {
        $cachedValue = $this->loadRules();
        $appliedRectorClasses = \array_unique(\array_merge($cachedValue, [$rectorClass]));
        $this->cache->save($this->getCacheKey(), CacheKey::KAIZEN_RULES, $appliedRectorClasses);
    }
    /**
     * @return array<class-string<RectorInterface>>
     */
    public function loadRules() : array
    {
        $key = $this->getCacheKey();
        $rules = $this->cache->load($key, CacheKey::KAIZEN_RULES) ?? [];
        if (!\is_array($rules)) {
            throw new ShouldNotHappenException();
        }
        return \array_unique($rules);
    }
    private function getCacheKey() : string
    {
        return CacheKey::KAIZEN_RULES . '_' . $this->fileHasher->hash(\getcwd());
    }
}
