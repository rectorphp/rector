<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeCollector\ScopeResolver;

use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
final class ParentClassScopeResolver
{
    public function resolveParentClassName(Scope $scope) : ?string
    {
        $parentClassReflection = $this->resolveParentClassReflection($scope);
        if (!$parentClassReflection instanceof ClassReflection) {
            return null;
        }
        return $parentClassReflection->getName();
    }
    public function resolveParentClassReflection(Scope $scope) : ?ClassReflection
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        return $classReflection->getParentClass();
    }
}
