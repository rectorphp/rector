<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
final class ArrayCompacter
{
    public function compactStringToVariableArray(\PhpParser\Node\Expr\Array_ $array) : void
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if ($arrayItem->key !== null) {
                continue;
            }
            if (!$arrayItem->value instanceof \PhpParser\Node\Scalar\String_) {
                continue;
            }
            $variableName = $arrayItem->value->value;
            $arrayItem->key = new \PhpParser\Node\Scalar\String_($variableName);
            $arrayItem->value = new \PhpParser\Node\Expr\Variable($variableName);
        }
    }
}
