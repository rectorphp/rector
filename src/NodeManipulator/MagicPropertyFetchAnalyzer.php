<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
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
final class MagicPropertyFetchAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private NodeTypeResolver $nodeTypeResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function isMagicOnType(PropertyFetch | StaticPropertyFetch $expr, Type $type): bool
    {
        $varNodeType = $this->nodeTypeResolver->resolve($expr);

        if ($varNodeType instanceof ErrorType) {
            return true;
        }

        if ($varNodeType instanceof MixedType) {
            return false;
        }

        if ($varNodeType->isSuperTypeOf($type)->yes()) {
            return false;
        }

        $nodeName = $this->nodeNameResolver->getName($expr->name);
        if ($nodeName === null) {
            return false;
        }

        return ! $this->hasPublicProperty($expr, $nodeName);
    }

    private function hasPublicProperty(PropertyFetch | StaticPropertyFetch $expr, string $propertyName): bool
    {
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            throw new ShouldNotHappenException();
        }

        if ($expr instanceof PropertyFetch) {
            $propertyFetchType = $scope->getType($expr->var);
        } else {
            $propertyFetchType = $this->nodeTypeResolver->resolve($expr->class);
        }

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

        $propertyReflection = $classReflection->getProperty($propertyName, $scope);
        return $propertyReflection->isPublic();
    }
}
