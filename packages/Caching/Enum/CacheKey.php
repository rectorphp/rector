<?php

declare(strict_types=1);

namespace Rector\Caching\Enum;

/**
 * @enum
 */
final class CacheKey
{
    /**
     * @var string
     */
    final public const CONFIGURATION_HASH_KEY = 'configuration_hash';

    /**
     * @var string
     */
    final public const FILE_HASH_KEY = 'file_hash';

    /**
     * @var string
     */
    final public const DEPENDENT_FILES_KEY = 'dependency_files_key';
}
