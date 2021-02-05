<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;

final class CompactFuncCallAnalyzer
{
    public function hasArrayDefinedVariableNames(Array_ $array, Scope $scope): bool
    {
        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if (! $arrayItem->value instanceof String_) {
                continue;
            }

            $variableName = $arrayItem->value->value;

            // the variable must not be defined here
            if ($scope->hasVariableType($variableName)->no()) {
                return false;
            }
        }

        return true;
    }
}
