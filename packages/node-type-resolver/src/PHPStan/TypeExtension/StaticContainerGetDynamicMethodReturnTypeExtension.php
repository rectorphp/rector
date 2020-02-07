<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\TypeExtension;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Psr\Container\ContainerInterface;

final class StaticContainerGetDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return ContainerInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'get';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $value = $methodCall->args[0]->value;
        $valueType = $scope->getType($value);

        // we don't know what it is
        if ($valueType instanceof MixedType) {
            return $valueType;
        }

        if ($valueType instanceof ConstantStringType) {
            return new ObjectType($valueType->getValue());
        }

        // unknown, probably variable
        return new MixedType();
    }
}
