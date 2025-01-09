<?php

declare (strict_types=1);
namespace Rector\Php74\Guard;

use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeAnalyzer\PropertyAnalyzer;
use Rector\NodeManipulator\PropertyManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Privatization\Guard\ParentPropertyLookupGuard;
final class PropertyTypeChangeGuard
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private PropertyAnalyzer $propertyAnalyzer;
    /**
     * @readonly
     */
    private PropertyManipulator $propertyManipulator;
    /**
     * @readonly
     */
    private ParentPropertyLookupGuard $parentPropertyLookupGuard;
    public function __construct(NodeNameResolver $nodeNameResolver, PropertyAnalyzer $propertyAnalyzer, PropertyManipulator $propertyManipulator, ParentPropertyLookupGuard $parentPropertyLookupGuard)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyAnalyzer = $propertyAnalyzer;
        $this->propertyManipulator = $propertyManipulator;
        $this->parentPropertyLookupGuard = $parentPropertyLookupGuard;
    }
    public function isLegal(Property $property, ClassReflection $classReflection, bool $inlinePublic = \true, bool $isConstructorPromotion = \false) : bool
    {
        if (\count($property->props) > 1) {
            return \false;
        }
        /**
         * - trait properties are unpredictable based on class context they appear in
         * - on interface properties as well, as interface not allowed to have property
         */
        if (!$classReflection->isClass()) {
            return \false;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        if ($this->propertyManipulator->hasTraitWithSamePropertyOrWritten($classReflection, $propertyName)) {
            return \false;
        }
        if ($this->propertyAnalyzer->hasForbiddenType($property)) {
            return \false;
        }
        if ($inlinePublic) {
            return \true;
        }
        if ($property->isPrivate()) {
            return \true;
        }
        if ($isConstructorPromotion) {
            return \true;
        }
        return $this->isSafeProtectedProperty($classReflection, $property);
    }
    private function isSafeProtectedProperty(ClassReflection $classReflection, Property $property) : bool
    {
        if (!$property->isProtected()) {
            return \false;
        }
        if (!$classReflection->isFinalByKeyword()) {
            return \false;
        }
        return $this->parentPropertyLookupGuard->isLegal($property, $classReflection);
    }
}
