<?php

declare (strict_types=1);
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
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer)
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
    public function isParentLockedProperty(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $classReflection = $this->resolveClassReflection($property);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                return \true;
            }
        }
        return \false;
    }
    public function isChildLockedProperty(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $classReflection = $this->resolveClassReflection($property);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($childrenClassReflections as $childClassReflection) {
            if ($childClassReflection === $classReflection) {
                continue;
            }
            if ($childClassReflection->hasProperty($propertyName)) {
                return \true;
            }
        }
        return \false;
    }
    private function resolveClassReflection(\PhpParser\Node $node) : ?\PHPStan\Reflection\ClassReflection
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        return $scope->getClassReflection();
    }
}
