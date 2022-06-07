<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
final class ArrayCompacter
{
    public function compactStringToVariableArray(Array_ $array) : void
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if ($arrayItem->key !== null) {
                continue;
            }
            if (!$arrayItem->value instanceof String_) {
                continue;
            }
            $variableName = $arrayItem->value->value;
            $arrayItem->key = new String_($variableName);
            $arrayItem->value = new Variable($variableName);
        }
    }
}
