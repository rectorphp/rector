<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyTypeVendorLockResolver extends AbstractNodeVendorLockResolver
{
    public function isVendorLocked(Property $property): bool
    {
        /** @var Scope $scope */
        $scope = $property->getAttribute(AttributeKey::SCOPE);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        if (! $this->hasParentClassChildrenClassesOrImplementsInterface($classReflection)) {
            return false;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);

        if ($this->isParentClassLocked($classReflection, $propertyName)) {
            return true;
        }

        return $this->isChildClassLocked($property, $classReflection, $propertyName);
    }

    private function isParentClassLocked(ClassReflection $classReflection, string $propertyName): bool
    {
        // extract to some "inherited parent method" service
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                // validate type is conflicting
                // parent class property in external scope → it's not ok
                return true;
            }
        }

        return false;
    }

    private function isChildClassLocked(
        Property $property,
        ClassReflection $classReflection,
        string $propertyName
    ): bool {
        if (! $classReflection->isClass()) {
            return false;
        }

        // is child class locked?
        if ($property->isPrivate()) {
            return false;
        }

        $childClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);

        foreach ($childClassReflections as $childClassReflection) {
            if (! $childClassReflection->hasProperty($propertyName)) {
                continue;
            }

            $propertyReflection = $childClassReflection->getNativeProperty($propertyName);

            // ensure the property is not in the parent class
            $propertyReflectionDeclaringClass = $propertyReflection->getDeclaringClass();
            if ($propertyReflectionDeclaringClass->getName() === $childClassReflection->getName()) {
                return true;
            }
        }

        return false;
    }
}
