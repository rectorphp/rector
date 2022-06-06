<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
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
