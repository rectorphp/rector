<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\Astral\ValueObject;

final class AttributeKey
{
    /**
     * Convention key name in php-parser and PHPStan for parent node
     *
     * @var string
     */
    public const PARENT = 'parent';
    /**
     * Used in php-paser, do not change
     *
     * @var string
     */
    public const KIND = 'kind';
    /**
     * @api
     * @var string
     */
    public const REFERENCED_CLASSES = 'referenced_classes';
    /**
     * Do not change, part of internal PHPStan naming
     *
     * @api
     * @var string
     */
    public const PREVIOUS = 'previous';
    /**
     * Do not change, part of internal PHPStan naming
     *
     * @api
     * @var string
     */
    public const NEXT = 'next';
    /**
     * Do not change, part of internal PHPStan naming
     *
     * @api
     * @var string
     */
    public const STATEMENT_DEPTH = 'statementDepth';
    /**
     * Used by php-parser, do not change
     *
     * @var string
     */
    public const COMMENTS = 'comments';
    /**
     * @var string
     */
    public const REFERENCED_CLASS_CONSTANTS = 'referenced_class_constants';
    /**
     * @var string
     */
    public const REFERENCED_METHOD_CALLS = 'referenced_method_calls';
}
