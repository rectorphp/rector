<?php

declare (strict_types=1);
namespace PHPStan\Type\PHPUnit;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use RectorPrefix20210510\PHPUnit\Framework\MockObject\MockBuilder;
class MockBuilderDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return MockBuilder::class;
    }
    public function isMethodSupported(MethodReflection $methodReflection) : bool
    {
        return !\in_array($methodReflection->getName(), ['getMock', 'getMockForAbstractClass', 'getMockForTrait'], \true);
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : Type
    {
        return $scope->getType($methodCall->var);
    }
}
