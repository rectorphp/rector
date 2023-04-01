<?php

declare (strict_types=1);
namespace PHPStan\Type\PHPUnit;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use RectorPrefix202304\PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use RectorPrefix202304\PHPUnit\Framework\MockObject\MockObject;
use function array_filter;
use function array_values;
use function count;
class MockObjectDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return MockObject::class;
    }
    public function isMethodSupported(MethodReflection $methodReflection) : bool
    {
        return $methodReflection->getName() === 'expects';
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : Type
    {
        $type = $scope->getType($methodCall->var);
        $mockClasses = array_values(array_filter($type->getObjectClassNames(), static function (string $class) : bool {
            return $class !== MockObject::class;
        }));
        if (count($mockClasses) !== 1) {
            return new ObjectType(InvocationMocker::class);
        }
        return new GenericObjectType(InvocationMocker::class, [new ObjectType($mockClasses[0])]);
    }
}
