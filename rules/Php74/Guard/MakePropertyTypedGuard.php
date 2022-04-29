<?php

declare (strict_types=1);
namespace Rector\Php74\Guard;

use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\NodeAnalyzer\PropertyAnalyzer;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\Guard\ParentPropertyLookupGuard;
final class MakePropertyTypedGuard
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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeAnalyzer\PropertyAnalyzer $propertyAnalyzer, \Rector\Core\NodeManipulator\PropertyManipulator $propertyManipulator, \Rector\Privatization\Guard\ParentPropertyLookupGuard $parentPropertyLookupGuard)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyAnalyzer = $propertyAnalyzer;
        $this->propertyManipulator = $propertyManipulator;
        $this->parentPropertyLookupGuard = $parentPropertyLookupGuard;
    }
    public function isLegal(\PhpParser\Node\Stmt\Property $property, bool $inlinePublic = \true) : bool
    {
        if ($property->type !== null) {
            return \false;
        }
        if (\count($property->props) > 1) {
            return \false;
        }
        $scope = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
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
        if ($inlinePublic) {
            return !$this->propertyAnalyzer->hasForbiddenType($property);
        }
        if ($property->isPrivate()) {
            return !$this->propertyAnalyzer->hasForbiddenType($property);
        }
        return $this->isSafeProtectedProperty($property, $classReflection);
    }
    private function isSafeProtectedProperty(\PhpParser\Node\Stmt\Property $property, \PHPStan\Reflection\ClassReflection $classReflection) : bool
    {
        if (!$property->isProtected()) {
            return \false;
        }
        if (!$classReflection->isFinal()) {
            return \false;
        }
        return $this->parentPropertyLookupGuard->isLegal($property);
    }
}
