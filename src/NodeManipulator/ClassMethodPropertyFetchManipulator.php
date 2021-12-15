<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ClassMethodPropertyFetchManipulator
{
    public function __construct(
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        private readonly NodeNameResolver $nodeNameResolver
    ) {
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
    public function findParamAssignToPropertyName(ClassMethod $classMethod, string $propertyName): ?Param
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

                if ($node->expr instanceof MethodCall || $node->expr instanceof StaticCall) {
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

    /**
     * E.g.:
     * $this->value = 1000;
     * ↓
     * (int $value)
     *
     * @return Expr[]
     */
    public function findAssignsToPropertyName(ClassMethod $classMethod, string $propertyName): array
    {
        $assignExprs = [];

        $paramNames = $this->getParamNames($classMethod);

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable(
            (array) $classMethod->stmts,
            function (Node $node) use ($propertyName, &$assignExprs, $paramNames): ?int {
                if (! $node instanceof Assign) {
                    return null;
                }

                if (! $this->nodeNameResolver->isName($node->var, $propertyName)) {
                    return null;
                }

                // skip param assigns
                if ($this->nodeNameResolver->isNames($node->expr, $paramNames)) {
                    return null;
                }

                $assignExprs[] = $node->expr;
                return null;
            }
        );

        return $assignExprs;
    }

    /**
     * @return string[]
     */
    private function getParamNames(ClassMethod $classMethod): array
    {
        $paramNames = [];
        foreach ($classMethod->getParams() as $param) {
            $paramNames[] = $this->nodeNameResolver->getName($param);
        }

        return $paramNames;
    }
}
