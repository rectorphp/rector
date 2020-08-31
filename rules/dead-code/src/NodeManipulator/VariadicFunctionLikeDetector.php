<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;

final class VariadicFunctionLikeDetector
{
    /**
     * @var string[]
     */
    private const VARIADIC_FUNCTION_NAMES = ['func_get_arg', 'func_get_args', 'func_num_args'];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    public function isVariadic(FunctionLike $functionLike): bool
    {
        $isVariadic = false;

        $this->callableNodeTraverser->traverseNodesWithCallable(
            (array) $functionLike->getStmts(),
            function (Node $node) use (&$isVariadic): ?int {
                if (! $node instanceof FuncCall) {
                    return null;
                }

                if (! $this->nodeNameResolver->isNames($node, self::VARIADIC_FUNCTION_NAMES)) {
                    return null;
                }

                $isVariadic = true;

                return NodeTraverser::STOP_TRAVERSAL;
            }
        );

        return $isVariadic;
    }
}
