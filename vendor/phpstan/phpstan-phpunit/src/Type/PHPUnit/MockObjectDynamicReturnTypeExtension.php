<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\Type\PHPUnit;

use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\PHPStan\Type\DynamicMethodReturnTypeExtension;
use RectorPrefix20220606\PHPStan\Type\Generic\GenericObjectType;
use RectorPrefix20220606\PHPStan\Type\IntersectionType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\TypeWithClassName;
use RectorPrefix20220606\PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use RectorPrefix20220606\PHPUnit\Framework\MockObject\MockObject;
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
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope) : ?\RectorPrefix20220606\PHPStan\Type\Type
    {
        $type = $scope->getType($methodCall->var);
        if (!$type instanceof IntersectionType) {
            return new ObjectType(InvocationMocker::class);
        }
        $mockClasses = array_values(array_filter($type->getTypes(), static function (Type $type) : bool {
            return !$type instanceof TypeWithClassName || $type->getClassName() !== MockObject::class;
        }));
        if (count($mockClasses) !== 1) {
            return new ObjectType(InvocationMocker::class);
        }
        return new GenericObjectType(InvocationMocker::class, $mockClasses);
    }
}
