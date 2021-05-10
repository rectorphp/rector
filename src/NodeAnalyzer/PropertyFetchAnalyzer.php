<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyFetchAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function isLocalPropertyFetch(Node $node): bool
    {
        if ($node instanceof PropertyFetch) {
            if ($node->var instanceof MethodCall) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->var, 'this');
        }

        if ($node instanceof StaticPropertyFetch) {
            return $this->nodeNameResolver->isName($node->class, 'self');
        }

        return false;
    }

    public function isLocalPropertyFetchName(Node $node, string $desiredPropertyName): bool
    {
        if (! $this->isLocalPropertyFetch($node)) {
            return false;
        }

        /** @var PropertyFetch|StaticPropertyFetch $node */
        return $this->nodeNameResolver->isName($node->name, $desiredPropertyName);
    }

    public function containsLocalPropertyFetchName(ClassMethod $classMethod, string $propertyName): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($classMethod, function (Node $node) use ($propertyName): bool {
            if (! $node instanceof PropertyFetch) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->name, $propertyName);
        });
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $expr
     */
    public function isPropertyToSelf(Expr $expr): bool
    {
        if ($expr instanceof PropertyFetch && ! $this->nodeNameResolver->isName($expr->var, 'this')) {
            return false;
        }

        if ($expr instanceof StaticPropertyFetch && ! $this->nodeNameResolver->isName($expr->class, 'self')) {
            return false;
        }

        $classLike = $expr->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        foreach ($classLike->getProperties() as $property) {
            if (! $this->nodeNameResolver->areNamesEqual($property->props[0], $expr)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Matches:
     * "$this->someValue = $<variableName>;"
     */
    public function isVariableAssignToThisPropertyFetch(Node $node, string $variableName): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->expr instanceof Variable) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node->expr, $variableName)) {
            return false;
        }

        return $this->isLocalPropertyFetch($node->var);
    }

    /**
     * @param string[] $propertyNames
     */
    public function isLocalPropertyOfNames(Node $node, array $propertyNames): bool
    {
        if (! $this->isLocalPropertyFetch($node)) {
            return false;
        }

        /** @var PropertyFetch $node */
        return $this->nodeNameResolver->isNames($node->name, $propertyNames);
    }
}
