<?php

declare (strict_types=1);
namespace Rector\Caching\ValueObject\Storage;

use Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface;
use Rector\Caching\ValueObject\CacheItem;
/**
 * inspired by https://github.com/phpstan/phpstan-src/blob/560652088406d7461c2c4ad4897784e33f8ab312/src/Cache/MemoryCacheStorage.php
 */
final class MemoryCacheStorage implements \Rector\Caching\Contract\ValueObject\Storage\CacheStorageInterface
{
    /**
     * @var array<string, CacheItem>
     */
    private $storage = [];
    /**
     * @return null|mixed
     * @param string $key
     * @param string $variableKey
     */
    public function load($key, $variableKey)
    {
        if (!isset($this->storage[$key])) {
            return null;
        }
        $item = $this->storage[$key];
        if (!$item->isVariableKeyValid($variableKey)) {
            return null;
        }
        return $item->getData();
    }
    /**
     * @param string $key
     * @param string $variableKey
     */
    public function save($key, $variableKey, $data) : void
    {
        $this->storage[$key] = new \Rector\Caching\ValueObject\CacheItem($variableKey, $data);
    }
    /**
     * @param string $key
     */
    public function clean($key) : void
    {
        if (!isset($this->storage[$key])) {
            return;
        }
        unset($this->storage[$key]);
    }
    public function clear() : void
    {
        $this->storage = [];
    }
}
