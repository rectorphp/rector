<?php

declare (strict_types=1);
namespace PHPStan\Type\PHPUnit;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use RectorPrefix202212\PHPUnit\Framework\MockObject\MockBuilder;
use function in_array;
class MockBuilderDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return MockBuilder::class;
    }
    public function isMethodSupported(MethodReflection $methodReflection) : bool
    {
        return !in_array($methodReflection->getName(), ['getMock', 'getMockForAbstractClass', 'getMockForTrait'], \true);
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : ?\PHPStan\Type\Type
    {
        return $scope->getType($methodCall->var);
    }
}
