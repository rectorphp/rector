<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php70\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
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
