<?php

declare(strict_types=1);

namespace Rector\Generics\Reflection;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;

final class ClassMethodAnalyzer
{
    public function hasClassMethodDirectly(
        ClassReflection $classReflection,
        string $methodName,
        Scope $scope
    ): bool {
        if (! $classReflection->hasMethod($methodName)) {
            return false;
        }

        $classMethodReflection = $classReflection->getMethod($methodName, $scope);
        $declaringClassReflection = $classMethodReflection->getDeclaringClass();

        return $declaringClassReflection->getName() === $classReflection->getName();
    }
}
