<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Node;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\Scope;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AttributeKey
{
    /**
     * @var string
     */
    public const DECLARES = 'declares';

    /**
     * @var string
     */
    public const VIRTUAL_NODE = 'virtual_node';

    /**
     * @var string
     */
    public const SCOPE = Scope::class;

    /**
     * @var string
     */
    public const NAMESPACE_NAME = 'namespace';

    /**
     * @var string
     */
    public const NAMESPACE_NODE = Namespace_::class;

    /**
     * @var string
     */
    public const USE_NODES = 'useNodes';

    /**
     * @var string
     */
    public const CLASS_NAME = 'className';

    /**
     * @var string
     */
    public const CLASS_NODE = ClassLike::class;

    /**
     * @var string
     */
    public const PARENT_CLASS_NAME = 'parentClassName';

    /**
     * @var string
     */
    public const METHOD_NAME = 'methodName';

    /**
     * @var string
     */
    public const METHOD_NODE = ClassMethod::class;

    /**
     * @var string
     */
    public const FUNCTION_NODE = Function_::class;

    /**
     * @var string
     */
    public const ORIGINAL_TYPE = 'originalType';

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
     * @var string
     */
    public const PREVIOUS_STATEMENT = 'previousExpression';

    /**
     * @var string
     */
    public const CURRENT_STATEMENT = 'currentExpression';

    /**
     * @var string
     */
    public const FILE_INFO = SmartFileInfo::class;

    /**
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const NAMESPACED_NAME = 'namespacedName';

    /**
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const DOC_INDENTATION = 'docIndentation';

    /**
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const START_TOKEN_POSITION = 'startTokenPos';

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
    public const PHP_DOC_INFO = PhpDocInfo::class;

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
    public const CLOSURE_NODE = Closure::class;

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
    public const IS_FIRST_LEVEL_STATEMENT = 'is_first_level_statement';

    /**
     * @var string
     */
    public const IS_FRESH_NODE = 'is_fresh_node';

    /**
     * @var string
     */
    public const FUNC_ARGS_TRAILING_COMMA = 'trailing_comma';

    /**
     * @var string
     */
    public const JUST_ADDED = 'just_added';
}
