<?php

declare (strict_types=1);
namespace Rector\Parallel\ValueObject;

/**
 * Helpers constant for passing constant names around
 */
final class Name
{
    /**
     * @var string
     */
    public const LINE = 'line';
    /**
     * @var string
     */
    public const MESSAGE = 'message';
    /**
     * @var string
     */
    public const RELATIVE_FILE_PATH = 'relative_file_path';
    /**
     * @var string
     */
    public const DIFF = 'diff';
    /**
     * @var string
     */
    public const DIFF_CONSOLE_FORMATTED = 'diff_console_formatted';
    /**
     * @var string
     */
    public const APPLIED_RECTORS = 'applied_rectors';
    /**
     * @var string
     */
    public const RECTOR_CLASS = 'rector_class';
}
