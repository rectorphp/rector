<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFinder;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
final class FormOnSuccessCallbackValuesParamFinder
{
    public function find(Class_ $class, Expr $onSuccessCallback) : ?Param
    {
        if ($onSuccessCallback instanceof Closure) {
            return $onSuccessCallback->params[1] ?? null;
        }
        $methodName = null;
        if ($onSuccessCallback instanceof Array_) {
            $varPart = $onSuccessCallback->items[0] ?? null;
            $methodNamePart = $onSuccessCallback->items[1] ?? null;
            if (!$varPart instanceof ArrayItem || !$methodNamePart instanceof ArrayItem) {
                return null;
            }
            if (!$varPart->value instanceof Variable) {
                return null;
            }
            if ($varPart->value->name !== 'this') {
                return null;
            }
            if (!$methodNamePart->value instanceof String_) {
                return null;
            }
            $methodName = $methodNamePart->value->value;
        }
        if ($methodName === null) {
            return null;
        }
        $classMethod = $class->getMethod($methodName);
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        return $classMethod->params[1] ?? null;
    }
}
