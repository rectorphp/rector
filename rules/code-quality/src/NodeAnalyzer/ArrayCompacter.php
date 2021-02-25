<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;

final class ArrayCompacter
{
    public function compactStringToVariableArray(Array_ $array): void
    {
        foreach ($array->items as $item) {
            if (! $item instanceof ArrayItem) {
                continue;
            }

            if ($item->key !== null) {
                continue;
            }

            if (! $item->value instanceof String_) {
                continue;
            }

            $variableName = $item->value->value;
            $item->key = new String_($variableName);
            $item->value = new Variable($variableName);
        }
    }
}
