<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Node;

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
    public const CLASS_SHORT_NAME = 'class_short_name';

    /**
     * @todo split Class node, interface node and trait node, to be compatible with other SpecificNode|null, values
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
     * Internal php-parser name.
     * Do not change this even if you want!
     *
     * @var string
     */
    public const ORIGINAL_NODE = 'origNode';

    /**
     * Internal php-parser name. @see \PhpParser\NodeVisitor\NameResolver
     * Do not change this even if you want!
     *
     * @var string
     */
    public const RESOLVED_NAME = 'resolvedName';

    /**
     * @var string
     */
    public const PARENT_NODE = 'parentNode';

    /**
     * @var string
     */
    public const PREVIOUS_NODE = 'prevNode';

    /**
     * @var string
     */
    public const NEXT_NODE = 'nextNode';

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
    public const IS_UNREACHABLE = 'is_unreachable';

    /**
     * @var string
     */
    public const FUNCTION_NODE = Function_::class;

    /**
     * @var string
     */
    public const PHP_DOC_INFO = PhpDocInfo::class;

    /**
     * @var string
     */
    public const METHOD_CALL_NODE_VARIABLE = 'method_call_variable';
}
