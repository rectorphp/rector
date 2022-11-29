<?php

declare (strict_types=1);
namespace Rector\Php74\Guard;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\PropertyAnalyzer;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\Guard\ParentPropertyLookupGuard;
final class PropertyTypeChangeGuard
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyAnalyzer
     */
    private $propertyAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Privatization\Guard\ParentPropertyLookupGuard
     */
    private $parentPropertyLookupGuard;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, PropertyAnalyzer $propertyAnalyzer, PropertyManipulator $propertyManipulator, ParentPropertyLookupGuard $parentPropertyLookupGuard, ReflectionResolver $reflectionResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyAnalyzer = $propertyAnalyzer;
        $this->propertyManipulator = $propertyManipulator;
        $this->parentPropertyLookupGuard = $parentPropertyLookupGuard;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isLegal(Property $property, bool $inlinePublic = \true, bool $isConstructorPromotion = \false) : bool
    {
        if (\count($property->props) > 1) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof ClassReflection) {
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
        if ($this->propertyManipulator->isUsedByTrait($classReflection, $propertyName)) {
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
        return $this->isSafeProtectedProperty($property);
    }
    private function isSafeProtectedProperty(Property $property) : bool
    {
        if (!$property->isProtected()) {
            return \false;
        }
        $parentNode = $property->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Class_) {
            throw new ShouldNotHappenException();
        }
        if (!$parentNode->isFinal()) {
            return \false;
        }
        return $this->parentPropertyLookupGuard->isLegal($property);
    }
}
