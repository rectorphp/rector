<?php

declare (strict_types=1);
namespace PHPStan\Type\PHPUnit;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use RectorPrefix20210510\PHPUnit\Framework\MockObject\Builder\InvocationMocker;
class InvocationMockerDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return InvocationMocker::class;
    }
    public function isMethodSupported(MethodReflection $methodReflection) : bool
    {
        return $methodReflection->getName() !== 'getMatcher';
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : Type
    {
        return $scope->getType($methodCall->var);
    }
}
