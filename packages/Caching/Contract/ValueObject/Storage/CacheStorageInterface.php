<?php

declare (strict_types=1);
namespace Rector\Caching\Contract\ValueObject\Storage;

/**
 * inspired by https://github.com/phpstan/phpstan-src/blob/560652088406d7461c2c4ad4897784e33f8ab312/src/Cache/CacheStorage.php
 * @internal
 */
interface CacheStorageInterface
{
    /**
     * @return mixed|null
     * @param string $key
     * @param string $variableKey
     */
    public function load($key, $variableKey);
    /**
     * @param mixed $data
     * @param string $key
     * @param string $variableKey
     */
    public function save($key, $variableKey, $data) : void;
    /**
     * @param string $key
     */
    public function clean($key) : void;
    public function clear() : void;
}
