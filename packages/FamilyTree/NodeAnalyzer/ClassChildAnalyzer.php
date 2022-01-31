<?php

declare (strict_types=1);
namespace Rector\FamilyTree\NodeAnalyzer;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
final class ClassChildAnalyzer
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    public function __construct(\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }
    public function hasChildClassMethod(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);
        foreach ($childrenClassReflections as $childClassReflection) {
            if (!$childClassReflection->hasNativeMethod($methodName)) {
                continue;
            }
            $constructorReflectionMethod = $childClassReflection->getNativeMethod($methodName);
            if (!$constructorReflectionMethod instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
                continue;
            }
            $methodDeclaringClassReflection = $constructorReflectionMethod->getDeclaringClass();
            if ($methodDeclaringClassReflection->getName() === $childClassReflection->getName()) {
                return \true;
            }
        }
        return \false;
    }
    public function hasParentClassMethod(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        return $this->resolveParentClassMethods($classReflection, $methodName) !== [];
    }
    public function hasAbstractParentClassMethod(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        $parentClassMethods = $this->resolveParentClassMethods($classReflection, $methodName);
        if ($parentClassMethods === []) {
            return \false;
        }
        foreach ($parentClassMethods as $parentClassMethod) {
            if ($parentClassMethod->isAbstract()) {
                return \true;
            }
        }
        return \false;
    }
    public function resolveParentClassMethodReturnType(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : \PHPStan\Type\Type
    {
        $parentClassMethods = $this->resolveParentClassMethods($classReflection, $methodName);
        if ($parentClassMethods === []) {
            return new \PHPStan\Type\MixedType();
        }
        foreach ($parentClassMethods as $parentClassMethod) {
            $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($parentClassMethod->getVariants());
            $nativeReturnType = $parametersAcceptor->getNativeReturnType();
            if (!$nativeReturnType instanceof \PHPStan\Type\MixedType) {
                return $nativeReturnType;
            }
        }
        return new \PHPStan\Type\MixedType();
    }
    /**
     * @return PhpMethodReflection[]
     */
    private function resolveParentClassMethods(\PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : array
    {
        $parentClassMethods = [];
        foreach ($classReflection->getParents() as $parentClassReflections) {
            if (!$parentClassReflections->hasMethod($methodName)) {
                continue;
            }
            $methodReflection = $parentClassReflections->getNativeMethod($methodName);
            if (!$methodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
                continue;
            }
            $methodDeclaringMethodClass = $methodReflection->getDeclaringClass();
            if ($methodDeclaringMethodClass->getName() === $parentClassReflections->getName()) {
                $parentClassMethods[] = $methodReflection;
            }
        }
        return $parentClassMethods;
    }
}
