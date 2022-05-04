<?php

declare(strict_types=1);

namespace Rector\Removing\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Removing\NodeAnalyzer\ForbiddenPropertyRemovalAnalyzer;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ComplexNodeRemover
{
    public function __construct(
        private readonly PropertyFetchFinder $propertyFetchFinder,
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly BetterNodeFinder $betterNodeFinder,
        private readonly NodeRemover $nodeRemover,
        private readonly NodeComparator $nodeComparator,
        private readonly ForbiddenPropertyRemovalAnalyzer $forbiddenPropertyRemovalAnalyzer,
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

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            $removeAssignSideEffect,
            $propertyName,
            &$hasSideEffect
        ) {
            // here should be checked all expr like stmts that can hold assign, e.f. if, foreach etc. etc.
            if ($node instanceof Expression) {
                $nodeExpr = $node->expr;

                // remove direct assigns
                if ($nodeExpr instanceof Assign) {
                    $assign = $nodeExpr;

                    // skip double assigns
                    if ($assign->expr instanceof Assign) {
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

        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($property);
        $assigns = [];

        foreach ($propertyFetches as $propertyFetch) {
            $assign = $this->resolvePropertyFetchAssign($propertyFetch);
            if (! $assign instanceof Assign) {
                return;
            }

            if ($assign->expr instanceof Assign) {
                return;
            }

            $assigns[] = $assign;
        }

        $this->processRemovePropertyAssigns($assigns);
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

    /**
     * @param Assign[] $assigns
     */
    private function processRemovePropertyAssigns(array $assigns): void
    {
        foreach ($assigns as $assign) {
            // remove assigns
            $this->removeConstructorDependency($assign);
        }
    }

    private function resolvePropertyFetchAssign(PropertyFetch | StaticPropertyFetch $expr): ?Assign
    {
        $assign = $expr->getAttribute(AttributeKey::PARENT_NODE);

        while ($assign instanceof Node && ! $assign instanceof Assign) {
            $assign = $assign->getAttribute(AttributeKey::PARENT_NODE);
        }

        if (! $assign instanceof Assign) {
            return null;
        }

        $isInExpr = (bool) $this->betterNodeFinder->findFirst(
            $assign->expr,
            fn (Node $subNode): bool => $this->nodeComparator->areNodesEqual($subNode, $expr)
        );

        if ($isInExpr) {
            return null;
        }

        $classLike = $this->betterNodeFinder->findParentType($expr, ClassLike::class);
        $propertyName = (string) $this->nodeNameResolver->getName($expr);

        if ($this->forbiddenPropertyRemovalAnalyzer->isForbiddenInNewCurrentClassNameSelfClone(
            $propertyName,
            $classLike
        )) {
            return null;
        }

        return $assign;
    }

    private function removeConstructorDependency(Assign $assign): void
    {
        $classMethod = $this->betterNodeFinder->findParentType($assign, ClassMethod::class);
        if (! $classMethod instanceof  ClassMethod) {
            return;
        }

        if (! $this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return;
        }

        $class = $this->betterNodeFinder->findParentType($assign, Class_::class);
        if (! $class instanceof Class_) {
            return;
        }

        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return;
        }

        $params = $constructClassMethod->getParams();
        $paramKeysToBeRemoved = [];

        /** @var Variable[] $variables */
        $variables = $this->resolveVariables($constructClassMethod);
        foreach ($params as $key => $param) {
            $variable = $this->betterNodeFinder->findFirst(
                (array) $constructClassMethod->stmts,
                fn (Node $node): bool => $this->nodeComparator->areNodesEqual($param->var, $node)
            );

            if (! $variable instanceof Node) {
                continue;
            }

            if ($this->isExpressionVariableNotAssign($variable)) {
                continue;
            }

            if (! $this->nodeComparator->areNodesEqual($param->var, $assign->expr)) {
                continue;
            }

            if ($this->isInVariables($variables, $assign)) {
                continue;
            }

            $paramKeysToBeRemoved[] = $key;
        }

        $this->processRemoveParamWithKeys($params, $paramKeysToBeRemoved);
    }

    /**
     * @return Variable[]
     */
    private function resolveVariables(ClassMethod $classMethod): array
    {
        return $this->betterNodeFinder->find(
            (array) $classMethod->stmts,
            function (Node $subNode): bool {
                if (! $subNode instanceof Variable) {
                    return false;
                }

                return $this->isExpressionVariableNotAssign($subNode);
            }
        );
    }

    /**
     * @param Variable[] $variables
     */
    private function isInVariables(array $variables, Assign $assign): bool
    {
        foreach ($variables as $variable) {
            if ($this->nodeComparator->areNodesEqual($assign->expr, $variable)) {
                return true;
            }
        }

        return false;
    }

    private function isExpressionVariableNotAssign(Node $node): bool
    {
        $expressionVariable = $node->getAttribute(AttributeKey::PARENT_NODE);
        return ! $expressionVariable instanceof Assign;
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
}
