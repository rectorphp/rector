<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Guard;

use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyTypeOverrideGuard
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver,
    ) {
    }

    public function isLegal(Property $property): bool
    {
        $propertyName = $this->nodeNameResolver->getName($property);

        $propertyScope = $property->getAttribute(AttributeKey::SCOPE);
        if (! $propertyScope instanceof Scope) {
            return true;
        }

        $classReflection = $propertyScope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

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
