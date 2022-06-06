<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\VendorLocker\NodeVendorLocker;

use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\Php\PhpPropertyReflection;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class PropertyTypeVendorLockResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }
    public function isVendorLocked(Property $property) : bool
    {
        $scope = $property->getAttribute(AttributeKey::SCOPE);
        // possibly trait
        if (!$scope instanceof Scope) {
            return \true;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if (\count($classReflection->getAncestors()) === 1) {
            return \false;
        }
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->isParentClassLocked($classReflection, $propertyName, $scope)) {
            return \true;
        }
        return $this->isChildClassLocked($property, $classReflection, $propertyName);
    }
    private function isParentClassLocked(ClassReflection $classReflection, string $propertyName, Scope $scope) : bool
    {
        $fileName = $classReflection->getFileName();
        // extract to some "inherited parent method" service
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasProperty($propertyName)) {
                continue;
            }
            if ($parentClassReflection->getFileName() === $fileName) {
                continue;
            }
            $property = $parentClassReflection->getProperty($propertyName, $scope);
            if (!$property instanceof PhpPropertyReflection) {
                // validate type is conflicting
                // parent class property in external scope → it's not ok
                return \true;
            }
            if ($property->getNativeType() instanceof MixedType) {
                // validate parent not typed yet → it's not ok
                return \true;
            }
        }
        return \false;
    }
    private function isChildClassLocked(Property $property, ClassReflection $classReflection, string $propertyName) : bool
    {
        if (!$classReflection->isClass()) {
            return \false;
        }
        // is child class locked?
        if ($property->isPrivate()) {
            return \false;
        }
        $childClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($childClassReflections as $childClassReflection) {
            if (!$childClassReflection->hasProperty($propertyName)) {
                continue;
            }
            $propertyReflection = $childClassReflection->getNativeProperty($propertyName);
            // ensure the property is not in the parent class
            $propertyReflectionDeclaringClass = $propertyReflection->getDeclaringClass();
            if ($propertyReflectionDeclaringClass->getName() === $childClassReflection->getName()) {
                return \true;
            }
        }
        return \false;
    }
}
