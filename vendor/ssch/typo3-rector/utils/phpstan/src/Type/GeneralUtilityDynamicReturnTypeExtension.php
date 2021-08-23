<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\Type;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class GeneralUtilityDynamicReturnTypeExtension implements \PHPStan\Type\DynamicStaticMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection $methodReflection
     */
    public function isStaticMethodSupported($methodReflection) : bool
    {
        return 'makeInstance' === $methodReflection->getName();
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection $methodReflection
     * @param \PhpParser\Node\Expr\StaticCall $methodCall
     * @param \PHPStan\Analyser\Scope $scope
     */
    public function getTypeFromStaticMethodCall($methodReflection, $methodCall, $scope) : \PHPStan\Type\Type
    {
        $arg = $methodCall->args[0]->value;
        if (!$arg instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }
        $class = $arg->class;
        return new \PHPStan\Type\ObjectType((string) $class);
    }
}
