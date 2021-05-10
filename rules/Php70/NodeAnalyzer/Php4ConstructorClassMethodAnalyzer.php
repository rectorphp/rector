<?php

declare (strict_types=1);
namespace Rector\Php70\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class Php4ConstructorClassMethodAnalyzer
{
    public function detect(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
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
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        return !$classReflection->isAnonymous();
    }
}
