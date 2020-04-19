<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyVendorLockResolver extends AbstractNodeVendorLockResolver
{
    public function isVendorLocked(Property $property): bool
    {
        /** @var Class_|null $classNode */
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if (! $this->hasParentClassOrImplementsInterface($classNode)) {
            return false;
        }

        /** @var string|null $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        if (! is_string($propertyName)) {
            throw new ShouldNotHappenException();
        }

        // @todo extract to some "inherited parent method" service
        /** @var string|null $parentClassName */
        $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $parentClassProperty = $this->findParentProperty($parentClassName, $propertyName);

            // @todo validate type is conflicting
            // parent class property in local scope → it's ok
            if ($parentClassProperty !== null) {
                return $parentClassProperty->type !== null;
            }

            // if not, look for it's parent parent - @todo recursion

            if (property_exists($parentClassName, $propertyName)) {
                // @todo validate type is conflicting
                // parent class property in external scope → it's not ok
                return true;

                // if not, look for it's parent parent - @todo recursion
            }
        }

        return false;
    }

    private function findParentProperty(string $parentClassName, string $propertyName): ?Property
    {
        $parentClassNode = $this->parsedNodeCollector->findClass($parentClassName);
        if ($parentClassNode === null) {
            return null;
        }

        return $parentClassNode->getProperty($propertyName);
    }
}
