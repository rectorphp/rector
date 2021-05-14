<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210514\Nette\Caching;

use RectorPrefix20210514\Nette;
/**
 * Output caching helper.
 */
class OutputHelper
{
    use Nette\SmartObject;
    /** @var array */
    public $dependencies = [];
    /** @var Cache|null */
    private $cache;
    /** @var string */
    private $key;
    public function __construct(\RectorPrefix20210514\Nette\Caching\Cache $cache, $key)
    {
        $this->cache = $cache;
        $this->key = $key;
        \ob_start();
    }
    /**
     * Stops and saves the cache.
     */
    public function end(array $dependencies = []) : void
    {
        if ($this->cache === null) {
            throw new \RectorPrefix20210514\Nette\InvalidStateException('Output cache has already been saved.');
        }
        $this->cache->save($this->key, \ob_get_flush(), $dependencies + $this->dependencies);
        $this->cache = null;
    }
    /**
     * Stops and throws away the output.
     */
    public function rollback() : void
    {
        \ob_end_flush();
        $this->cache = null;
    }
}
