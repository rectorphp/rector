<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionProperty;

final class PropertyTypeVendorLockResolver extends AbstractNodeVendorLockResolver
{
    public function isVendorLocked(Property $property): bool
    {
        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        /** @var Class_|Interface_ $classLike */
        if (! $this->hasParentClassChildrenClassesOrImplementsInterface($classLike)) {
            return false;
        }

        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);

        if ($this->isParentClassLocked($classLike, $propertyName)) {
            return true;
        }

        return $this->isChildClassLocked($property, $classLike, $propertyName);
    }

    /**
     * @param Class_|Interface_ $classLike
     */
    private function isParentClassLocked(ClassLike $classLike, string $propertyName): bool
    {
        if (! $classLike instanceof Class_) {
            return false;
        }

        // extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classLike->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return false;
        }

        // if not, look for it's parent parent - recursion

        if (property_exists($parentClassName, $propertyName)) {
            // validate type is conflicting
            // parent class property in external scope → it's not ok
            return true;

            // if not, look for it's parent parent
        }

        return false;
    }

    /**
     * @param Class_|Interface_ $classLike
     */
    private function isChildClassLocked(Property $property, ClassLike $classLike, string $propertyName): bool
    {
        if (! $classLike instanceof Class_) {
            return false;
        }

        // is child class locker
        if ($property->isPrivate()) {
            return false;
        }

        $childrenClassNames = $this->getChildrenClassesByClass($classLike);

        foreach ($childrenClassNames as $childClassName) {
            if (! property_exists($childClassName, $propertyName)) {
                continue;
            }

            // ensure the property is not in the parent class
            $reflectionProperty = new ReflectionProperty($childClassName, $propertyName);
            if ($reflectionProperty->class !== $childClassName) {
                continue;
            }

            return true;
        }

        return false;
    }
}
