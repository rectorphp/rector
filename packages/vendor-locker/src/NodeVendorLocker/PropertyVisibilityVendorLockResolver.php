<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class PropertyVisibilityVendorLockResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    public function __construct(NodeNameResolver $nodeNameResolver, FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }

    /**
     * Checks for:
     * - child classes required properties
     *
     * Prevents:
     * - changing visibility conflicting with children
     */
    public function isParentLockedProperty(Property $property): bool
    {
        $classReflection = $this->resolveClassReflection($property);
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        $propertyName = $this->nodeNameResolver->getName($property);

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                return true;
            }
        }

        return false;
    }

    public function isChildLockedProperty(Property $property): bool
    {
        $classReflection = $this->resolveClassReflection($property);
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        $propertyName = $this->nodeNameResolver->getName($property);

        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($childrenClassReflections as $childClassReflection) {
            if ($childClassReflection === $classReflection) {
                continue;
            }
            $childrenClassReflectionHasProperty = $childClassReflection->hasProperty($propertyName);

            if ($childrenClassReflectionHasProperty) {
                return true;
            }
        }

        return false;
    }

    private function resolveClassReflection(Node $node): ?ClassReflection
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        return $scope->getClassReflection();
    }
}
