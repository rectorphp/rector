<?php

declare (strict_types=1);
namespace Rector\Caching\Contract;

/**
 * Allows extensions to provide additional metadata for cache invalidation.
 * When any extension's hash changes, all cached files are reprocessed.
 *
 * @api
 */
interface CacheMetaExtensionInterface
{
    /**
     * Returns unique key for this cache meta entry.
     * This describes the source of the metadata.
     */
    public function getKey(): string;
    /**
     * Returns hash of the cache meta entry.
     * This represents the current state of the additional meta source.
     */
    public function getHash(): string;
}
