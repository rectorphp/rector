<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
final class MethodTypeDetector
{
    public function isTestMethod(ClassMethod $classMethod, Scope $scope): bool
    {
        $classMethodName = $classMethod->name->toString();
        if (strncmp($classMethodName, 'test', strlen('test')) === 0) {
            return \true;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $extendedMethodReflection = $classReflection->getMethod($classMethodName, $scope);
        if ($extendedMethodReflection->getDocComment() === null) {
            return \false;
        }
        return strpos($extendedMethodReflection->getDocComment(), '@test') !== \false;
    }
    public function isTraitMethod(ClassMethod $classMethod, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $extendedMethodReflection = $classReflection->getMethod($classMethod->name->toString(), $scope);
        if ($extendedMethodReflection instanceof PhpMethodReflection) {
            return $extendedMethodReflection->getDeclaringTrait() instanceof ClassReflection;
        }
        return \false;
    }
}
