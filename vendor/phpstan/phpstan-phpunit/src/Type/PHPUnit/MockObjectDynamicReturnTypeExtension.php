<?php

declare (strict_types=1);
namespace PHPStan\Type\PHPUnit;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use RectorPrefix20220501\PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use RectorPrefix20220501\PHPUnit\Framework\MockObject\MockObject;
use function array_filter;
use function array_values;
use function count;
class MockObjectDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return \RectorPrefix20220501\PHPUnit\Framework\MockObject\MockObject::class;
    }
    public function isMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return $methodReflection->getName() === 'expects';
    }
    public function getTypeFromMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\MethodCall $methodCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        $type = $scope->getType($methodCall->var);
        if (!$type instanceof \PHPStan\Type\IntersectionType) {
            return new \PHPStan\Type\ObjectType(\RectorPrefix20220501\PHPUnit\Framework\MockObject\Builder\InvocationMocker::class);
        }
        $mockClasses = \array_values(\array_filter($type->getTypes(), static function (\PHPStan\Type\Type $type) : bool {
            return !$type instanceof \PHPStan\Type\TypeWithClassName || $type->getClassName() !== \RectorPrefix20220501\PHPUnit\Framework\MockObject\MockObject::class;
        }));
        if (\count($mockClasses) !== 1) {
            return new \PHPStan\Type\ObjectType(\RectorPrefix20220501\PHPUnit\Framework\MockObject\Builder\InvocationMocker::class);
        }
        return new \PHPStan\Type\Generic\GenericObjectType(\RectorPrefix20220501\PHPUnit\Framework\MockObject\Builder\InvocationMocker::class, $mockClasses);
    }
}
