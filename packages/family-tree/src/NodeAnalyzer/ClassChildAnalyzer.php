<?php

declare(strict_types=1);

namespace Rector\FamilyTree\NodeAnalyzer;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;

final class ClassChildAnalyzer
{
    public function hasChildClassMethod(ClassReflection $classReflection, string $methodName): bool
    {
        foreach ($classReflection->getAncestors() as $childClassReflection) {
            if ($classReflection === $childClassReflection) {
                continue;
            }

            if (! $childClassReflection->hasMethod($methodName)) {
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
        foreach ($classReflection->getParents() as $parentClassReflections) {
            if (! $parentClassReflections->hasMethod($methodName)) {
                continue;
            }

            $constructMethodReflection = $parentClassReflections->getNativeMethod($methodName);
            if (! $constructMethodReflection instanceof PhpMethodReflection) {
                continue;
            }

            $methodDeclaringMethodClass = $constructMethodReflection->getDeclaringClass();
            if ($methodDeclaringMethodClass->getName() === $parentClassReflections->getName()) {
                return true;
            }
        }

        return false;
    }
}
