<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\Type\PHPUnit;

use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Type\DynamicMethodReturnTypeExtension;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPUnit\Framework\MockObject\Builder\InvocationMocker;
class InvocationMockerDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return InvocationMocker::class;
    }
    public function isMethodSupported(MethodReflection $methodReflection) : bool
    {
        return $methodReflection->getName() !== 'getMatcher';
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : ?\RectorPrefix20220606\PHPStan\Type\Type
    {
        return $scope->getType($methodCall->var);
    }
}
