<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Node;

/**
 * @enum
 */
final class AttributeKey
{
    /**
     * Internal php-parser key for String_, LNumber and DNumber nodes to hold original value (with "_" separators etc.)
     * @var string
     */
    public const RAW_VALUE = 'rawValue';
    /**
     * @var string
     */
    public const VIRTUAL_NODE = 'virtual_node';
    /**
     * @var string
     */
    public const SCOPE = 'scope';
    /**
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const ORIGINAL_NODE = 'origNode';
    /**
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const COMMENTS = 'comments';
    /**
     * Cover multi docs
     * @var string
     */
    public const PREVIOUS_DOCS_AS_COMMENTS = 'previous_docs_as_comments';
    /**
     * Cover multi docs
     * @var string
     */
    public const NEW_MAIN_DOC = 'new_main_doc';
    /**
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const ORIGINAL_NAME = 'originalName';
    /**
     * Internal php-parser name. @see \PhpParser\NodeVisitor\NameResolver
     * Do not change this even if you want!
     *
     * @var string
     */
    public const RESOLVED_NAME = 'resolvedName';
    /**
     * @internal of php-parser, do not change
     * @see https://github.com/nikic/PHP-Parser/pull/681/files
     * @var string
     */
    public const PARENT_NODE = 'parent';
    /**
     * @internal of php-parser, do not change
     * @see https://github.com/nikic/PHP-Parser/pull/681/files
     * @var string
     */
    public const PREVIOUS_NODE = 'previous';
    /**
     * @internal of php-parser, do not change
     * @see https://github.com/nikic/PHP-Parser/pull/681/files
     * @var string
     */
    public const NEXT_NODE = 'next';
    /**
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const NAMESPACED_NAME = 'namespacedName';
    /**
     * @api
     *
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const DOC_INDENTATION = 'docIndentation';
    /**
     * @var string
     * Use often in php-parser
     */
    public const KIND = 'kind';
    /**
     * @var string
     */
    public const IS_UNREACHABLE = 'isUnreachable';
    /**
     * @var string
     */
    public const PHP_DOC_INFO = 'php_doc_info';
    /**
     * @var string
     */
    public const IS_REGULAR_PATTERN = 'is_regular_pattern';
    /**
     * @var string
     */
    public const DO_NOT_CHANGE = 'do_not_change';
    /**
     * @var string
     */
    public const PARAMETER_POSITION = 'parameter_position';
    /**
     * @var string
     */
    public const ARGUMENT_POSITION = 'argument_position';
    /**
     * @var string
     */
    public const FUNC_ARGS_TRAILING_COMMA = 'trailing_comma';
    /**
     * Helps with infinite loop detection
     * @var string
     */
    public const CREATED_BY_RULE = 'created_by_rule';
    /**
     * @var string
     */
    public const WRAPPED_IN_PARENTHESES = 'wrapped_in_parentheses';
    /**
     * @var string
     */
    public const COMMENT_CLOSURE_RETURN_MIRRORED = 'comment_closure_return_mirrored';
    /**
     * To pass PHP 8.0 attribute FQN names
     * @var string
     */
    public const PHP_ATTRIBUTE_NAME = 'php_attribute_name';
    /**
     * Helper attribute to reprint raw value of int/float/string
     * @var string
     */
    public const REPRINT_RAW_VALUE = 'reprint_raw_value';
    /**
     * @var string
     */
    public const EXTRA_USE_IMPORT = 'extra_use_import';
    /**
     * @var string
     */
    public const DOC_LABEL = 'docLabel';
    /**
     * Prints array in newlined fastion, one item per line
     * @var string
     */
    public const NEWLINED_ARRAY_PRINT = 'newlined_array_print';
    /**
     * @var string
     */
    public const ASSIGNED_TO = 'assigned_to';
}
