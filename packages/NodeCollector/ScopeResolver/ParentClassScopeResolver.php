<?php

declare(strict_types=1);

namespace Rector\NodeCollector\ScopeResolver;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;

final class ParentClassScopeResolver
{
<<<<<<< HEAD
    public function resolveParentClassName(Scope $scope): ?string
=======
    public function resolveParentClassReflection(Node $node): ?ClassReflection
>>>>>>> NativeFunctionReflection has new parameter
    {
        $parentClassReflection = $this->resolveParentClassReflection($scope);
        if (! $parentClassReflection instanceof ClassReflection) {
            return null;
        }

        return $parentClassReflection->getName();
    }

    public function resolveParentClassReflection(Scope $scope): ?ClassReflection
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

<<<<<<< HEAD
        $parentClassReflection = $classReflection->getParentClass();
<<<<<<< HEAD
        if ($parentClassReflection instanceof ClassReflection) {
            return $parentClassReflection;
=======
        if ($parentClassReflection === null) {
=======
        return $classReflection->getParentClass();
    }

    public function resolveParentClassName(Node $node): ?string
    {
        $classReflection = $this->resolveParentClassReflection($node);
        if (! $classReflection instanceof ClassReflection) {
>>>>>>> NativeFunctionReflection has new parameter
            return null;
>>>>>>> PHPStan\Reflection\ClassReflection::getParentClass now returns null|class reflection
        }

<<<<<<< HEAD
        return null;
=======
        return $classReflection->getName();
>>>>>>> NativeFunctionReflection has new parameter
    }
}
