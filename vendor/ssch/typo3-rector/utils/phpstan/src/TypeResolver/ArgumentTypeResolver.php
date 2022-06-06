<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\PHPStan\TypeResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
final class ArgumentTypeResolver
{
    public function resolveFromMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall, \PHPStan\Reflection\MethodReflection $methodReflection) : \PHPStan\Type\Type
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
