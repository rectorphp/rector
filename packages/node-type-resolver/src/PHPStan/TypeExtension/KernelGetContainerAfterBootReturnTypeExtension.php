<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\TypeExtension;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Symfony\Component\HttpKernel\Kernel;

final class KernelGetContainerAfterBootReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Kernel::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getContainer';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

        if (! $this->isCalledAfterBoot($scope, $methodCall)) {
            return $returnType;
        }

        if ($returnType instanceof UnionType) {
            foreach ($returnType->getTypes() as $singleType) {
                if ($singleType instanceof ObjectType) {
                    return $singleType;
                }
            }
        }

        return $returnType;
    }

    private function isCalledAfterBoot(Scope $scope, MethodCall $methodCall): bool
    {
        $kernelBootMethodCall = new MethodCall($methodCall->var, 'boot');

        return ! $scope->getType($kernelBootMethodCall) instanceof ErrorType;
    }
}
