<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\ReturnTypeExtension;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * @copied from https://github.com/phpstan/phpstan-nette/blob/master/src/Type/Nette/ComponentModelDynamicReturnTypeExtension.php
 */
final class ComponentModelDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return 'Nette\ComponentModel\Container';
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getComponent';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $calledOnType = $scope->getType($methodCall->var);
        $mixedType = new MixedType();
        $args = $methodCall->args;
        if (count($args) !== 1) {
            return $mixedType;
        }

        $argType = $scope->getType($args[0]->value);
        if (! $argType instanceof ConstantStringType) {
            return $mixedType;
        }

        $componentName = ucfirst($argType->getValue());
        $methodName = sprintf('createComponent%s', $componentName);
        if (! $calledOnType->hasMethod($methodName)->yes()) {
            return $mixedType;
        }

        $method = $calledOnType->getMethod($methodName, $scope);

        return ParametersAcceptorSelector::selectSingle($method->getVariants())->getReturnType();
    }
}
