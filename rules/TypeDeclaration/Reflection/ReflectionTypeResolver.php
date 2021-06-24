<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Reflection;

use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ReflectionTypeResolver
{
    public function __construct(
        private NodeTypeResolver $nodeTypeResolver,
        private ReflectionProvider $reflectionProvider,
        private NodeNameResolver $nodeNameResolver,
    ) {
    }

    public function resolvePropertyFetchType(PropertyFetch $propertyFetch): ?Type
    {
        $objectType = $this->nodeTypeResolver->resolve($propertyFetch->var);
        if (! $objectType instanceof TypeWithClassName) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        if ($propertyName === null) {
            return null;
        }

        if ($classReflection->hasProperty($propertyName)) {
            $propertyFetchScope = $propertyFetch->getAttribute(AttributeKey::SCOPE);
            $propertyReflection = $classReflection->getProperty($propertyName, $propertyFetchScope);

            if ($propertyReflection instanceof PhpPropertyReflection) {
                return $propertyReflection->getNativeType();
            }
        }

        return null;
    }
}
