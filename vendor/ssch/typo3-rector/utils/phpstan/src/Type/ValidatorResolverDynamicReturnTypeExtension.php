<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\Type;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
final class ValidatorResolverDynamicReturnTypeExtension implements \PHPStan\Type\DynamicMethodReturnTypeExtension
{
    public function getClass() : string
    {
        return 'TYPO3\\CMS\\Extbase\\Validation\\ValidatorResolver\\ValidatorResolver';
    }
    public function isMethodSupported(\PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        return 'createValidator' === $methodReflection->getName();
    }
    public function getTypeFromMethodCall(\PHPStan\Reflection\MethodReflection $methodReflection, \PhpParser\Node\Expr\MethodCall $methodCall, \PHPStan\Analyser\Scope $scope) : \PHPStan\Type\Type
    {
        $arg = $methodCall->args[0]->value;
        if (!$arg instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }
        /** @var Name $class */
        $class = $arg->class;
        return \PHPStan\Type\TypeCombinator::addNull(new \PHPStan\Type\ObjectType((string) $class));
    }
}
