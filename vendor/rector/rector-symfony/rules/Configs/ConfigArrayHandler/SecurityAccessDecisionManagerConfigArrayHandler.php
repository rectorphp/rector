<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\ConfigArrayHandler;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
final class SecurityAccessDecisionManagerConfigArrayHandler
{
    /**
     * @return array<Expression<MethodCall>>
     */
    public function handle(Array_ $array, Variable $variable, string $mainMethodName) : array
    {
        if (!$array->items[0] instanceof ArrayItem) {
            return [];
        }
        $configurationArrayItem = $array->items[0];
        $nestedArray = $configurationArrayItem->value;
        if (!$nestedArray instanceof Array_) {
            return [];
        }
        // build accessControl() method call here
        $accessDecisionManagerMethodCall = new MethodCall($variable, $mainMethodName);
        foreach ($nestedArray->items as $nestedArrayItem) {
            if (!$nestedArrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$nestedArrayItem->key instanceof String_) {
                continue;
            }
            $methodNameString = $nestedArrayItem->key;
            $methodName = $methodNameString->value;
            $args = [new Arg($nestedArrayItem->value)];
            $accessDecisionManagerMethodCall = new MethodCall($accessDecisionManagerMethodCall, $methodName, $args);
        }
        return [new Expression($accessDecisionManagerMethodCall)];
    }
}
