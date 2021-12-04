<?php

declare(strict_types=1);

namespace Rector\Parallel\ValueObject;

/**
 * Helpers constant for passing constant names around
 */
final class Name
{
    /**
     * @var string
     */
    final public const LINE = 'line';

    /**
     * @var string
     */
    final public const MESSAGE = 'message';

    /**
     * @var string
     */
    final public const RELATIVE_FILE_PATH = 'relative_file_path';

    /**
     * @var string
     */
    final public const DIFF = 'diff';

    /**
     * @var string
     */
    final public const DIFF_CONSOLE_FORMATTED = 'diff_console_formatted';

    /**
     * @var string
     */
    final public const APPLIED_RECTORS = 'applied_rectors';
}
