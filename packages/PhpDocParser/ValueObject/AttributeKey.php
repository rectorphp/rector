<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\ValueObject;

/**
 * @api
 */
final class AttributeKey
{
    /**
     * Used in php-paser, do not change
     *
     * @var string
     */
    public const KIND = 'kind';
    /**
     * Used by php-parser, do not change
     *
     * @var string
     */
    public const COMMENTS = 'comments';
    /**
     * PHPStan @api Used in PHPStan for printed node content. Useful for printing error messages without need to reprint
     * it again.
     *
     * @var string
     */
    public const PHPSTAN_CACHE_PRINTER = 'phpstan_cache_printer';
    /**
     * @var string
     */
    public const ASSIGNED_TO = 'assigned_to';
    /**
     * @var string
     */
    public const NULLSAFE_CHECKED = 'nullsafe_checked';
    /**
     * PHPStan @api
     *
     * @var string
     */
    public const PARENT_STMT_TYPES = 'parentStmtTypes';
}
