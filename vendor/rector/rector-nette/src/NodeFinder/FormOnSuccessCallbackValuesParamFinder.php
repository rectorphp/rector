<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFinder;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
final class FormOnSuccessCallbackValuesParamFinder
{
    public function find(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Expr $onSuccessCallback) : ?\PhpParser\Node\Param
    {
        if ($onSuccessCallback instanceof \PhpParser\Node\Expr\Closure) {
            return $onSuccessCallback->params[1] ?? null;
        }
        $methodName = null;
        if ($onSuccessCallback instanceof \PhpParser\Node\Expr\Array_) {
            /** @var Expr\ArrayItem|null $varPart */
            $varPart = $onSuccessCallback->items[0] ?? null;
            $methodNamePart = $onSuccessCallback->items[1] ?? null;
            if ($varPart === null || $methodNamePart === null) {
                return null;
            }
            if (!$varPart->value instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if ($varPart->value->name !== 'this') {
                return null;
            }
            if (!$methodNamePart->value instanceof \PhpParser\Node\Scalar\String_) {
                return null;
            }
            $methodName = $methodNamePart->value->value;
        }
        if ($methodName === null) {
            return null;
        }
        $classMethod = $class->getMethod($methodName);
        if ($classMethod === null) {
            return null;
        }
        return $classMethod->params[1] ?? null;
    }
}
