<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
final class LocalArrayMethodCallableMatcher
{
    public function match(Expr $expr) : ?string
    {
        if ($expr instanceof MethodCall) {
            if (!$expr->name instanceof Identifier) {
                return null;
            }
            return $expr->name->toString();
        }
        if ($expr instanceof Array_) {
            $firstItem = $expr->items[0];
            if (!$firstItem->value instanceof Variable) {
                return null;
            }
            $methodName = $firstItem->value->name;
            if (!\is_string($methodName)) {
                return null;
            }
            if ($methodName !== 'this') {
                return null;
            }
            $secondItem = $expr->items[1];
            if (!$secondItem->value instanceof String_) {
                return null;
            }
            return $secondItem->value->value;
        }
        return null;
    }
}
