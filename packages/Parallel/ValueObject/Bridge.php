<?php

declare(strict_types=1);

namespace Rector\Parallel\ValueObject;

/**
 * @enum
 */
final class Bridge
{
    /**
     * @var string
     */
    final public const FILE_DIFFS = 'file_diffs';

    /**
     * @var string
     */
    final public const SYSTEM_ERRORS = 'system_errors';

    /**
     * @var string
     */
    final public const SYSTEM_ERRORS_COUNT = 'system_errors_count';

    /**
     * @var string
     */
    final public const FILES = 'files';

    /**
     * @var string
     */
    final public const FILES_COUNT = 'files_count';
}
