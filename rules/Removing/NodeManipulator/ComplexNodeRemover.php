<?php

declare(strict_types=1);

namespace Rector\Removing\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\AssignRemover;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;

final class ComplexNodeRemover
{
    public function __construct(
        private AssignRemover $assignRemover,
        private PropertyFetchFinder $propertyFetchFinder,
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder,
        private NodeRemover $nodeRemover,
        private NodesToRemoveCollector $nodesToRemoveCollector,
        private NodeComparator $nodeComparator
    ) {
    }

    /**
     * @param string[] $classMethodNamesToSkip
     */
    public function removePropertyAndUsages(Property $property, array $classMethodNamesToSkip = []): void
    {
        $shouldKeepProperty = false;

        $propertyFetches = $this->propertyFetchFinder->findPrivatePropertyFetches($property);

        foreach ($propertyFetches as $propertyFetch) {
            if ($this->shouldSkipPropertyForClassMethod($propertyFetch, $classMethodNamesToSkip)) {
                $shouldKeepProperty = true;
                continue;
            }

            $assign = $this->resolveAssign($propertyFetch);
            if (! $assign instanceof Assign) {
                return;
            }

            // remove assigns
            $this->assignRemover->removeAssignNode($assign);
            $this->removeConstructorDependency($assign);
        }

        if ($shouldKeepProperty) {
            return;
        }

        // remove __construct param

        /** @var Property $property */
        $this->nodeRemover->removeNode($property);

        foreach ($property->props as $prop) {
            if (! $this->nodesToRemoveCollector->isNodeRemoved($prop)) {
                // if the property has at least one node left -> return
                return;
            }
        }

        $this->nodeRemover->removeNode($property);
    }

    /**
     * @param StaticPropertyFetch|PropertyFetch $expr
     * @param string[] $classMethodNamesToSkip
     */
    private function shouldSkipPropertyForClassMethod(Expr $expr, array $classMethodNamesToSkip): bool
    {
        $classMethodNode = $expr->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethodNode instanceof ClassMethod) {
            return false;
        }

        $classMethodName = $this->nodeNameResolver->getName($classMethodNode);
        return in_array($classMethodName, $classMethodNamesToSkip, true);
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $expr
     */
    private function resolveAssign(Expr $expr): ?Assign
    {
        $assign = $expr->getAttribute(AttributeKey::PARENT_NODE);

        while ($assign !== null && ! $assign instanceof Assign) {
            $assign = $assign->getAttribute(AttributeKey::PARENT_NODE);
        }

        if (! $assign instanceof Assign) {
            return null;
        }

        return $assign;
    }

    private function removeConstructorDependency(Assign $assign): void
    {
        $classMethod = $assign->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof  ClassMethod) {
            return;
        }

        if (! $this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return;
        }

        $class = $assign->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return;
        }

        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return;
        }

        foreach ($constructClassMethod->getParams() as $param) {
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

            $this->nodeRemover->removeNode($param);
        }
    }

    private function isExpressionVariableNotAssign(Node $node): bool
    {
        if ($node !== null) {
            $expressionVariable = $node->getAttribute(AttributeKey::PARENT_NODE);

            if (! $expressionVariable instanceof Assign) {
                return true;
            }
        }

        return false;
    }
}
