<?php

declare(strict_types=1);

namespace Rector\FamilyTree\NodeAnalyzer;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;

final class ClassChildAnalyzer
{
    public function __construct(
        private readonly FamilyRelationsAnalyzer $familyRelationsAnalyzer
    ) {
    }

    public function hasChildClassMethod(ClassReflection $classReflection, string $methodName): bool
    {
        $childrenClassReflections = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);

        foreach ($childrenClassReflections as $childClassReflection) {
            if (! $childClassReflection->hasNativeMethod($methodName)) {
                continue;
            }

            $constructorReflectionMethod = $childClassReflection->getNativeMethod($methodName);
            if (! $constructorReflectionMethod instanceof PhpMethodReflection) {
                continue;
            }

            $methodDeclaringClassReflection = $constructorReflectionMethod->getDeclaringClass();
            if ($methodDeclaringClassReflection->getName() === $childClassReflection->getName()) {
                return true;
            }
        }

        return false;
    }

    public function hasParentClassMethod(ClassReflection $classReflection, string $methodName): bool
    {
        return $this->resolveParentClassMethods($classReflection, $methodName) !== [];
    }

    public function hasAbstractParentClassMethod(ClassReflection $classReflection, string $methodName): bool
    {
        $parentClassMethods = $this->resolveParentClassMethods($classReflection, $methodName);
        if ($parentClassMethods === []) {
            return false;
        }

        foreach ($parentClassMethods as $parentClassMethod) {
            if ($parentClassMethod->isAbstract()) {
                return true;
            }
        }

        return false;
    }

    public function resolveParentClassMethodReturnType(ClassReflection $classReflection, string $methodName): Type
    {
        $parentClassMethods = $this->resolveParentClassMethods($classReflection, $methodName);
        if ($parentClassMethods === []) {
            return new MixedType();
        }

        foreach ($parentClassMethods as $parentClassMethod) {
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($parentClassMethod->getVariants());
            $nativeReturnType = $parametersAcceptor->getNativeReturnType();

            if (! $nativeReturnType instanceof MixedType) {
                return $nativeReturnType;
            }
        }

        return new MixedType();
    }

    /**
     * @return PhpMethodReflection[]
     */
    private function resolveParentClassMethods(ClassReflection $classReflection, string $methodName): array
    {
        $parentClassMethods = [];
        foreach ($classReflection->getParents() as $parentClassReflections) {
            if (! $parentClassReflections->hasMethod($methodName)) {
                continue;
            }

            $methodReflection = $parentClassReflections->getNativeMethod($methodName);
            if (! $methodReflection instanceof PhpMethodReflection) {
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
