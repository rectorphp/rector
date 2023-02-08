<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\TypeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
/**
 * @api
 */
final class ClassMethodReturnTypeResolver
{
    public function resolve(ClassMethod $classMethod, Scope $scope) : Type
    {
        $methodName = $classMethod->name->toString();
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return new MixedType();
        }
        $extendedMethodReflection = $classReflection->getMethod($methodName, $scope);
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($extendedMethodReflection, $classMethod, $scope);
        if (!$parametersAcceptor instanceof FunctionVariant) {
            return new MixedType();
        }
        return $parametersAcceptor->getReturnType();
    }
}
