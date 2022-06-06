<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\Type\PHPUnit;

use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Type\DynamicMethodReturnTypeExtension;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPUnit\Framework\MockObject\MockBuilder;
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
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : ?\RectorPrefix20220606\PHPStan\Type\Type
    {
        return $scope->getType($methodCall->var);
    }
}
