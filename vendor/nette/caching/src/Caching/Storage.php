<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210514\Nette\Caching;

/**
 * Cache storage.
 */
interface Storage
{
    /**
     * Read from cache.
     * @return mixed
     */
    function read(string $key);
    /**
     * Prevents item reading and writing. Lock is released by write() or remove().
     */
    function lock(string $key) : void;
    /**
     * Writes item into the cache.
     */
    function write(string $key, $data, array $dependencies) : void;
    /**
     * Removes item from the cache.
     */
    function remove(string $key) : void;
    /**
     * Removes items from the cache by conditions.
     */
    function clean(array $conditions) : void;
}
\class_exists(\RectorPrefix20210514\Nette\Caching\IStorage::class);
