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
    public function resolveFromMethodCall(MethodCall $methodCall, MethodReflection $methodReflection) : Type
    {
        $arg = $methodCall->args[0]->value;
        if (!$arg instanceof ClassConstFetch) {
            return ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
        }
        $class = $arg->class;
        if ($class instanceof Expr) {
            return new MixedType();
        }
        return new ObjectType($class->toString());
    }
}
