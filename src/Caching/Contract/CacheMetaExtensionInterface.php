<?php

declare (strict_types=1);
namespace Rector\Caching\Contract;

/**
 * @api
 *
 * @deprecated Niche mechanism, no longer applied. Let Rector handle cache on its own. If custom
 * invalidation is needed, handle it in CI in a more generic way, e.g. by clearing the cache directory.
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
