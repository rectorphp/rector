<?php

declare (strict_types=1);
namespace Rector\Parallel\ValueObject;

/**
 * @api
 * Helpers constant for passing constant names around
 */
final class BridgeItem
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
    /**
     * @var string
     */
    public const RECTORS_WITH_LINE_CHANGES = 'rectors_with_line_changes';
}
