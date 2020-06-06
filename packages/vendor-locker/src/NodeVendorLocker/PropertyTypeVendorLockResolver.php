<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyTypeVendorLockResolver extends AbstractNodeVendorLockResolver
{
    public function isVendorLocked(Property $property): bool
    {
        /** @var Class_|null $classNode */
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        /** @var Class_|Interface_ $classNode */
        if (! $this->hasParentClassChildrenClassesOrImplementsInterface($classNode)) {
            return false;
        }

        /** @var string|null $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        if (! is_string($propertyName)) {
            throw new ShouldNotHappenException();
        }

        if ($this->isParentClassLocked($classNode, $propertyName)) {
            return true;
        }

        return $this->isChildClassLocked($property, $classNode, $propertyName);
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
            if (property_exists($childClassName, $propertyName)) {
                return true;
            }
        }

        return false;
    }
}
