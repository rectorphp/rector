<?php

declare (strict_types=1);
namespace Rector\Caching\Enum;

/**
 * @enum
 */
final class CacheKey
{
    /**
     * @var string
     */
    public const CONFIGURATION_HASH_KEY = 'configuration_hash';
    /**
     * @var string
     */
    public const FILE_HASH_KEY = 'file_hash';
}
