<?php

declare (strict_types=1);
namespace Rector\Php70\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
final class Php4ConstructorClassMethodAnalyzer
{
    public function detect(ClassMethod $classMethod, Scope $scope) : bool
    {
        // catch only classes without namespace
        if ($scope->getNamespace() !== null) {
            return \false;
        }
        if ($classMethod->isAbstract()) {
            return \false;
        }
        if ($classMethod->isStatic()) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return !$classReflection->isAnonymous();
    }
}
