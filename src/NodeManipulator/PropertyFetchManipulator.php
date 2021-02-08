<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * Utils for PropertyFetch Node:
 * "$this->property"
 */
final class PropertyFetchManipulator
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        ReflectionProvider $reflectionProvider
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isPropertyToSelf(PropertyFetch $propertyFetch): bool
    {
        if (! $this->nodeNameResolver->isName($propertyFetch->var, 'this')) {
            return false;
        }

        $classLike = $propertyFetch->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        foreach ($classLike->getProperties() as $property) {
            if (! $this->nodeNameResolver->areNamesEqual($property->props[0], $propertyFetch)) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function isMagicOnType(PropertyFetch $propertyFetch, Type $type): bool
    {
        $varNodeType = $this->nodeTypeResolver->resolve($propertyFetch);

        if ($varNodeType instanceof ErrorType) {
            return true;
        }

        if ($varNodeType instanceof MixedType) {
            return false;
        }

        if ($varNodeType->isSuperTypeOf($type)->yes()) {
            return false;
        }

        $nodeName = $this->nodeNameResolver->getName($propertyFetch);
        if ($nodeName === null) {
            return false;
        }

        return ! $this->hasPublicProperty($propertyFetch, $nodeName);
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

    public function isLocalPropertyFetch(Node $node): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        return $this->nodeNameResolver->isName($node->var, 'this');
    }

    private function hasPublicProperty(PropertyFetch $propertyFetch, string $propertyName): bool
    {
        $nodeScope = $propertyFetch->getAttribute(AttributeKey::SCOPE);
        if (! $nodeScope instanceof Scope) {
            throw new ShouldNotHappenException();
        }

        $propertyFetchType = $nodeScope->getType($propertyFetch->var);
        if (! $propertyFetchType instanceof TypeWithClassName) {
            return false;
        }

        $propertyFetchType = $propertyFetchType->getClassName();
        if (! $this->reflectionProvider->hasClass($propertyFetchType)) {
            return false;
        }

        $classReflection = $this->reflectionProvider->getClass($propertyFetchType);
        if (! $classReflection->hasProperty($propertyName)) {
            return false;
        }

        $propertyReflection = $classReflection->getProperty($propertyName, $nodeScope);

        return $propertyReflection->isPublic();
    }
}
