<?php

declare (strict_types=1);
namespace Rector\CodingStyle\NodeFactory;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
final class ArrayCallableToMethodCallFactory
{
    public function create(\PhpParser\Node\Expr\Array_ $array) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (\count($array->items) !== 2) {
            return null;
        }
        $firstItem = $array->items[0];
        $secondItem = $array->items[1];
        if (!$firstItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        if (!$secondItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        if (!$secondItem->value instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        if (!$firstItem->value instanceof \PhpParser\Node\Expr\PropertyFetch && !$firstItem->value instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        $string = $secondItem->value;
        $methodName = $string->value;
        return new \PhpParser\Node\Expr\MethodCall($firstItem->value, $methodName);
    }
}
