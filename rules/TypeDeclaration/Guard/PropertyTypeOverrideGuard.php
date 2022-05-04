<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Guard;

use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;

final class PropertyTypeOverrideGuard
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
        private readonly ReflectionResolver $reflectionResolver
    ) {
    }

    public function isLegal(Property $property): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);

        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        $propertyName = $this->nodeNameResolver->getName($property);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $nativeReflectionClass = $parentClassReflection->getNativeReflection();

            if (! $nativeReflectionClass->hasProperty($propertyName)) {
                continue;
            }

            $parentPropertyReflection = $nativeReflectionClass->getProperty($propertyName);

            // empty type override is not allowed
            return $parentPropertyReflection->getType() !== null;
        }

        return true;
    }
}
