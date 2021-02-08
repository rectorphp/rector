<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ClassMethodPropertyFetchManipulator
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * In case the property name is different to param name:
     *
     * E.g.:
     * (SomeType $anotherValue)
     * $this->value = $anotherValue;
     * ↓
     * (SomeType $anotherValue)
     */
    public function resolveParamForPropertyFetch(ClassMethod $classMethod, string $propertyName): ?Param
    {
        $assignedParamName = null;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use ($propertyName, &$assignedParamName): ?int {
                if (! $node instanceof Assign) {
                    return null;
                }

                if (! $this->nodeNameResolver->isName($node->var, $propertyName)) {
                    return null;
                }

                if (StaticInstanceOf::isOneOf($node->expr, [MethodCall::class, StaticCall::class])) {
                    return null;
                }

                $assignedParamName = $this->nodeNameResolver->getName($node->expr);

                return NodeTraverser::STOP_TRAVERSAL;
            }
        );

        /** @var string|null $assignedParamName */
        if ($assignedParamName === null) {
            return null;
        }

        /** @var Param $param */
        foreach ($classMethod->params as $param) {
            if (! $this->nodeNameResolver->isName($param, $assignedParamName)) {
                continue;
            }

            return $param;
        }

        return null;
    }
}
