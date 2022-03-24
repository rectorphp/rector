<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\Type;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class GeneralUtilityDynamicReturnTypeExtension implements \PHPStan\Type\DynamicStaticMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return 'TYPO3\\CMS\\Core\\Utility\\GeneralUtility';
    }
    public function isStaticMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return 'makeInstance' === $methodReflection->getName();
    }
    public function getTypeFromStaticMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\StaticCall $methodCall, \PHPStan\Analyser\Scope $scope) : ?\PHPStan\Type\Type
    {
        $arg = $methodCall->args[0]->value;
        if (!$arg instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }
        $class = $arg->class;
        if ($class instanceof \PhpParser\Node\Expr) {
            return new \PHPStan\Type\MixedType();
        }
        return new \PHPStan\Type\ObjectType($class->toString());
    }
}
