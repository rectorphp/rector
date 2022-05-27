<?php

declare (strict_types=1);
namespace Rector\NodeCollector\ScopeResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
final class ParentClassScopeResolver
{
    public function resolveParentClassName(\PHPStan\Analyser\Scope $scope) : ?string
    {
        $parentClassReflection = $this->resolveParentClassReflection($scope);
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        return $parentClassReflection->getName();
    }
    public function resolveParentClassReflection(\PHPStan\Analyser\Scope $scope) : ?\PHPStan\Reflection\ClassReflection
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        return $classReflection->getParentClass();
    }
}
