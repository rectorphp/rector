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
     */
    public function load(string $key, string $variableKey);
    /**
     * @param mixed $data
     */
    public function save(string $key, string $variableKey, $data) : void;
    public function clean(string $key) : void;
    public function clear() : void;
}
