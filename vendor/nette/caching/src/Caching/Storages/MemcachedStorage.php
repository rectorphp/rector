<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210514\Nette\Caching\Storages;

use RectorPrefix20210514\Nette;
use RectorPrefix20210514\Nette\Caching\Cache;
/**
 * Memcached storage using memcached extension.
 */
class MemcachedStorage implements \RectorPrefix20210514\Nette\Caching\Storage, \RectorPrefix20210514\Nette\Caching\BulkReader
{
    use Nette\SmartObject;
    /** @internal cache structure */
    private const META_CALLBACKS = 'callbacks', META_DATA = 'data', META_DELTA = 'delta';
    /** @var \Memcached */
    private $memcached;
    /** @var string */
    private $prefix;
    /** @var Journal */
    private $journal;
    /**
     * Checks if Memcached extension is available.
     */
    public static function isAvailable() : bool
    {
        return \extension_loaded('memcached');
    }
    public function __construct(string $host = 'localhost', int $port = 11211, string $prefix = '', \RectorPrefix20210514\Nette\Caching\Storages\Journal $journal = null)
    {
        if (!static::isAvailable()) {
            throw new \RectorPrefix20210514\Nette\NotSupportedException("PHP extension 'memcached' is not loaded.");
        }
        $this->prefix = $prefix;
        $this->journal = $journal;
        $this->memcached = new \Memcached();
        if ($host) {
            $this->addServer($host, $port);
        }
    }
    public function addServer(string $host = 'localhost', int $port = 11211) : void
    {
        if (@$this->memcached->addServer($host, $port, 1) === \false) {
            // @ is escalated to exception
            $error = \error_get_last();
            throw new \RectorPrefix20210514\Nette\InvalidStateException("Memcached::addServer(): {$error['message']}.");
        }
    }
    public function getConnection() : \Memcached
    {
        return $this->memcached;
    }
    public function read(string $key)
    {
        $key = \urlencode($this->prefix . $key);
        $meta = $this->memcached->get($key);
        if (!$meta) {
            return null;
        }
        // meta structure:
        // array(
        //     data => stored data
        //     delta => relative (sliding) expiration
        //     callbacks => array of callbacks (function, args)
        // )
        // verify dependencies
        if (!empty($meta[self::META_CALLBACKS]) && !\RectorPrefix20210514\Nette\Caching\Cache::checkCallbacks($meta[self::META_CALLBACKS])) {
            $this->memcached->delete($key, 0);
            return null;
        }
        if (!empty($meta[self::META_DELTA])) {
            $this->memcached->replace($key, $meta, $meta[self::META_DELTA] + \time());
        }
        return $meta[self::META_DATA];
    }
    public function bulkRead(array $keys) : array
    {
        $prefixedKeys = \array_map(function ($key) {
            return \urlencode($this->prefix . $key);
        }, $keys);
        $keys = \array_combine($prefixedKeys, $keys);
        $metas = $this->memcached->getMulti($prefixedKeys);
        $result = [];
        $deleteKeys = [];
        foreach ($metas as $prefixedKey => $meta) {
            if (!empty($meta[self::META_CALLBACKS]) && !\RectorPrefix20210514\Nette\Caching\Cache::checkCallbacks($meta[self::META_CALLBACKS])) {
                $deleteKeys[] = $prefixedKey;
            } else {
                $result[$keys[$prefixedKey]] = $meta[self::META_DATA];
            }
            if (!empty($meta[self::META_DELTA])) {
                $this->memcached->replace($prefixedKey, $meta, $meta[self::META_DELTA] + \time());
            }
        }
        if (!empty($deleteKeys)) {
            $this->memcached->deleteMulti($deleteKeys, 0);
        }
        return $result;
    }
    public function lock(string $key) : void
    {
    }
    public function write(string $key, $data, array $dp) : void
    {
        if (isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::ITEMS])) {
            throw new \RectorPrefix20210514\Nette\NotSupportedException('Dependent items are not supported by MemcachedStorage.');
        }
        $key = \urlencode($this->prefix . $key);
        $meta = [self::META_DATA => $data];
        $expire = 0;
        if (isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::EXPIRATION])) {
            $expire = (int) $dp[\RectorPrefix20210514\Nette\Caching\Cache::EXPIRATION];
            if (!empty($dp[\RectorPrefix20210514\Nette\Caching\Cache::SLIDING])) {
                $meta[self::META_DELTA] = $expire;
                // sliding time
            }
        }
        if (isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::CALLBACKS])) {
            $meta[self::META_CALLBACKS] = $dp[\RectorPrefix20210514\Nette\Caching\Cache::CALLBACKS];
        }
        if (isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::TAGS]) || isset($dp[\RectorPrefix20210514\Nette\Caching\Cache::PRIORITY])) {
            if (!$this->journal) {
                throw new \RectorPrefix20210514\Nette\InvalidStateException('CacheJournal has not been provided.');
            }
            $this->journal->write($key, $dp);
        }
        $this->memcached->set($key, $meta, $expire);
    }
    public function remove(string $key) : void
    {
        $this->memcached->delete(\urlencode($this->prefix . $key), 0);
    }
    public function clean(array $conditions) : void
    {
        if (!empty($conditions[\RectorPrefix20210514\Nette\Caching\Cache::ALL])) {
            $this->memcached->flush();
        } elseif ($this->journal) {
            foreach ($this->journal->clean($conditions) as $entry) {
                $this->memcached->delete($entry, 0);
            }
        }
    }
}
