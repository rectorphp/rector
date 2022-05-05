<?php

declare(strict_types=1);

namespace Rector\Removing\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ComplexNodeRemover
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly NodeRemover $nodeRemover,
        private readonly SideEffectNodeDetector $sideEffectNodeDetector,
        private readonly SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
    ) {
    }

    public function removePropertyAndUsages(
        Class_ $class,
        Property $property,
        bool $removeAssignSideEffect
    ): void {
        $propertyName = $this->nodeNameResolver->getName($property);

        $hasSideEffect = false;
        $isPartoFAnotherAssign = false;

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            $removeAssignSideEffect,
            $propertyName,
            &$hasSideEffect,
            &$isPartoFAnotherAssign
        ) {
            // here should be checked all expr like stmts that can hold assign, e.f. if, foreach etc. etc.
            if ($node instanceof Expression) {
                $nodeExpr = $node->expr;

                // remove direct assigns
                if ($nodeExpr instanceof Assign) {
                    $assign = $nodeExpr;

                    // skip double assigns
                    if ($assign->expr instanceof Assign) {
                        $isPartoFAnotherAssign = true;
                        return null;
                    }

                    $propertyFetches = $this->resolvePropertyFetchFromDimFetch($assign->var);
                    if ($propertyFetches === []) {
                        return null;
                    }

                    foreach ($propertyFetches as $propertyFetch) {
                        if (! $this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
                            continue;
                        }

                        if ($this->nodeNameResolver->isName($propertyFetch->name, $propertyName)) {
                            if (! $removeAssignSideEffect && $this->sideEffectNodeDetector->detect($assign->expr)) {
                                $hasSideEffect = true;
                                return null;
                            }

                            $this->nodeRemover->removeNode($node);
                        }
                    }
                }
            }

            return null;
        });

        // do not remove anyhting in case of side-effect
        if ($hasSideEffect) {
            return;
        }

        if ($isPartoFAnotherAssign) {
            return;
        }

        $this->removeConstructorDependency($class, $propertyName);

        $this->nodeRemover->removeNode($property);
    }

    /**
     * @param Param[] $params
     * @param int[] $paramKeysToBeRemoved
     * @return int[]
     */
    public function processRemoveParamWithKeys(array $params, array $paramKeysToBeRemoved): array
    {
        $totalKeys = count($params) - 1;
        $removedParamKeys = [];

        foreach ($paramKeysToBeRemoved as $paramKeyToBeRemoved) {
            $startNextKey = $paramKeyToBeRemoved + 1;
            for ($nextKey = $startNextKey; $nextKey <= $totalKeys; ++$nextKey) {
                if (! isset($params[$nextKey])) {
                    // no next param, break the inner loop, remove the param
                    break;
                }

                if (in_array($nextKey, $paramKeysToBeRemoved, true)) {
                    // keep searching next key not in $paramKeysToBeRemoved
                    continue;
                }

                return [];
            }

            $this->nodeRemover->removeNode($params[$paramKeyToBeRemoved]);
            $removedParamKeys[] = $paramKeyToBeRemoved;
        }

        return $removedParamKeys;
    }

    private function removeConstructorDependency(Class_ $class, string $propertyName): void
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $classMethod instanceof ClassMethod) {
            return;
        }

        $stmts = (array) $classMethod->stmts;

        foreach ($stmts as $key => $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $stmtExpr = $stmt->expr;
            if ($stmtExpr instanceof Assign && $stmtExpr->var instanceof PropertyFetch) {
                $propertyFetch = $stmtExpr->var;
                if ($this->nodeNameResolver->isName($propertyFetch, $propertyName)) {
                    unset($classMethod->stmts[$key]);

                    if ($stmtExpr->expr instanceof Variable) {
                        $this->clearParamFromConstructor($classMethod, $stmtExpr->expr);
                    }
                }
            }
        }
    }

    /**
     * @return PropertyFetch[]
     */
    private function resolvePropertyFetchFromDimFetch(Expr $expr): array
    {
        // unwrap array dim fetch, till we get to parent too caller node
        $propertyFetches = [];

        while ($expr instanceof ArrayDimFetch) {
            if ($expr->dim instanceof PropertyFetch) {
                $propertyFetches[] = $expr->dim;
            }

            $expr = $expr->var;
        }

        if ($expr instanceof PropertyFetch) {
            $propertyFetches[] = $expr;
        }

        return $propertyFetches;
    }

    private function clearParamFromConstructor(ClassMethod $classMethod, Variable $assignedVariable): void
    {
        // is variable used somewhere else? skip it
        $variables = $this->betterNodeFinder->findInstanceOf($classMethod, Variable::class);

        $paramNamedVariables = array_filter(
            $variables,
            fn (Variable $variable): bool => $this->nodeNameResolver->areNamesEqual($variable, $assignedVariable)
        );

        // there is more than 1 use, keep it in the constructor
        if (count($paramNamedVariables) > 1) {
            return;
        }

        $paramName = $this->nodeNameResolver->getName($assignedVariable);
        if (! is_string($paramName)) {
            return;
        }

        foreach ($classMethod->params as $paramKey => $param) {
            if ($this->nodeNameResolver->isName($param->var, $paramName)) {
                unset($classMethod->params[$paramKey]);
            }
        }
    }
}
