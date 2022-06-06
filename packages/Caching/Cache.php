<?php

declare (strict_types=1);
namespace Rector\Caching;

use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
final class Cache
{
    /**
     * @readonly
     * @var \Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface
     */
    private $cacheStorage;
    public function __construct(\Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface $cacheStorage)
    {
        $this->cacheStorage = $cacheStorage;
    }
    /**
     * @return mixed|null
     */
    public function load(string $key, string $variableKey)
    {
        return $this->cacheStorage->load($key, $variableKey);
    }
    /**
     * @param mixed $data
     */
    public function save(string $key, string $variableKey, $data) : void
    {
        $this->cacheStorage->save($key, $variableKey, $data);
    }
    public function clear() : void
    {
        $this->cacheStorage->clear();
    }
    public function clean(string $cacheKey) : void
    {
        $this->cacheStorage->clean($cacheKey);
    }
}
