<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Type\TypeExtension;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Psr\Container\ContainerInterface;
use Rector\Exception\ShouldNotHappenException;

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
        $valueType = $scope->getType($methodCall->args[0]->value);

        // we don't know what it is
        if ($valueType instanceof MixedType) {
            return $valueType;
        }

        if ($valueType instanceof ConstantStringType) {
            return new ObjectType($valueType->getValue());
        }

        throw new ShouldNotHappenException(sprintf(
            '%s type given, only "%s" is supported',
            get_class($valueType),
            ConstantStringType::class
        ));
    }
}
