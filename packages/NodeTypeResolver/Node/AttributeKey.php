<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\Node;

use PHPStan\Analyser\Scope;
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
     * Contains @see Scope
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
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const ORIGINAL_NAME = 'originalName';
    /**
     * @deprecated Refactor to a custom visitors/parent node instead,
     * @see https://phpstan.org/blog/preprocessing-ast-for-custom-rules
     *
     * @internal of php-parser, do not change
     * @see https://github.com/nikic/PHP-Parser/pull/681/files
     * @var string
     *
     * @api for BC layer
     *
     * The parent node can be still enabled by using custom PHPStan configuration,
     * @see https://github.com/rectorphp/rector-src/pull/4458#discussion_r1257478146
     */
    public const PARENT_NODE = 'parent';
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
    public const IS_ASSIGNED_TO = 'is_assigned_to';
    /**
     * @var string
     */
    public const IS_GLOBAL_VAR = 'is_global_var';
    /**
     * @var string
     */
    public const IS_STATIC_VAR = 'is_static_var';
    /**
     * @var string
     */
    public const IS_BYREF_VAR = 'is_byref_var';
    /**
     * @var string
     */
    public const IS_BYREF_RETURN = 'is_byref_return';
    /**
     * @var string
     */
    public const STMT_KEY = 'stmt_key';
    /**
     * @var string
     */
    public const IS_BEING_ASSIGNED = 'is_being_assigned';
    /**
     * @var string
     */
    public const IS_MULTI_ASSIGN = 'is_multi_assign';
    /**
     * @var string
     */
    public const STATEMENT_DEPTH = 'statementDepth';
    /**
     * @var string
     */
    public const EXPRESSION_DEPTH = 'expressionDepth';
    /**
     * @var string
     */
    public const IS_IN_LOOP = 'is_in_loop';
    /**
     * @var string
     */
    public const IS_VARIABLE_LOOP = 'is_variable_loop';
    /**
     * @var string
     */
    public const IS_IN_IF = 'is_in_if';
    /**
     * @var string
     */
    public const IS_UNSET_VAR = 'is_unset_var';
    /**
     * @var string
     */
    public const IS_ARRAY_IN_ATTRIBUTE = 'is_array_in_attribute';
    /**
     * @var string
     */
    public const IS_NAMESPACE_NAME = 'is_namespace_name';
    /**
     * @var string
     */
    public const IS_USEUSE_NAME = 'is_useuse_name';
    /**
     * @var string
     */
    public const IS_STATICCALL_CLASS_NAME = 'is_staticcall_class_name';
    /**
     * @var string
     */
    public const IS_FUNCCALL_NAME = 'is_funccall_name';
    /**
     * @var string
     */
    public const IS_CONSTFETCH_NAME = 'is_constfetch_name';
    /**
     * @var string
     */
    public const IS_NEW_INSTANCE_NAME = 'is_new_instance_name';
    /**
     * @var string
     */
    public const IS_ARG_VALUE = 'is_arg_value';
    /**
     * @var string
     */
    public const IS_PARAM_VAR = 'IS_PARAM_VAR';
    /**
     * @var string
     */
    public const IS_CLASS_EXTENDS = 'is_class_extends';
    /**
     * @var string
     */
    public const IS_CLASS_IMPLEMENT = 'is_class_implement';
    /**
     * @var string
     */
    public const FROM_FUNC_CALL_NAME = 'from_func_call_name';
    /**
     * @var string
     */
    public const INSIDE_ARRAY_DIM_FETCH = 'inside_array_dim_fetch';
    /**
     * @var string
     */
    public const IS_USED_AS_ARG_BY_REF_VALUE = 'is_used_as_arg_by_ref_value';
}
