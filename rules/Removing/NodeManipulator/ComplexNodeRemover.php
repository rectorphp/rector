<?php

declare(strict_types=1);

namespace Rector\Removing\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\AssignRemover;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Removing\NodeAnalyzer\ForbiddenPropertyRemovalAnalyzer;

final class ComplexNodeRemover
{
    public function __construct(
        private AssignRemover $assignRemover,
        private PropertyFetchFinder $propertyFetchFinder,
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder,
        private NodeRemover $nodeRemover,
        private NodeComparator $nodeComparator,
        private ForbiddenPropertyRemovalAnalyzer $forbiddenPropertyRemovalAnalyzer
    ) {
    }

    /**
     * @param string[] $classMethodNamesToSkip
     */
    public function removePropertyAndUsages(Property $property, array $classMethodNamesToSkip = []): void
    {
        $shouldKeepProperty = false;

        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($property);
        $assigns = [];
        foreach ($propertyFetches as $propertyFetch) {
            if ($this->shouldSkipPropertyForClassMethod($propertyFetch, $classMethodNamesToSkip)) {
                $shouldKeepProperty = true;
                continue;
            }

            $assign = $this->resolveAssign($propertyFetch);
            if (! $assign instanceof Assign) {
                return;
            }

            $assigns[] = $assign;
        }

        $this->processRemovePropertyAssigns($assigns);

        if ($shouldKeepProperty) {
            return;
        }

        $this->nodeRemover->removeNode($property);
    }

    /**
     * @param Assign[] $assigns
     */
    private function processRemovePropertyAssigns(array $assigns): void
    {
        foreach ($assigns as $assign) {
            // remove assigns
            $this->assignRemover->removeAssignNode($assign);
            $this->removeConstructorDependency($assign);
        }
    }

    /**
     * @param string[] $classMethodNamesToSkip
     */
    private function shouldSkipPropertyForClassMethod(
        StaticPropertyFetch | PropertyFetch $expr,
        array $classMethodNamesToSkip
    ): bool {
        $classMethodNode = $this->betterNodeFinder->findParentType($expr, ClassMethod::class);
        if (! $classMethodNode instanceof ClassMethod) {
            return false;
        }

        $classMethodName = $this->nodeNameResolver->getName($classMethodNode);
        return in_array($classMethodName, $classMethodNamesToSkip, true);
    }

    private function resolveAssign(PropertyFetch | StaticPropertyFetch $expr): ?Assign
    {
        $assign = $expr->getAttribute(AttributeKey::PARENT_NODE);

        while ($assign !== null && ! $assign instanceof Assign) {
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

            $paramKeysToBeRemoved[] = $key;
        }

        $this->processRemoveParamWithKeys($params, $paramKeysToBeRemoved);
    }

    /**
     * @param Param[] $params
     * @param int[] $paramKeysToBeRemoved
     */
    private function processRemoveParamWithKeys(array $params, array $paramKeysToBeRemoved): void
    {
        $totalKeys = count($params) - 1;
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

                return;
            }

            $this->nodeRemover->removeNode($params[$paramKeyToBeRemoved]);
        }
    }

    private function isExpressionVariableNotAssign(Node $node): bool
    {
        $expressionVariable = $node->getAttribute(AttributeKey::PARENT_NODE);
        return ! $expressionVariable instanceof Assign;
    }
}
