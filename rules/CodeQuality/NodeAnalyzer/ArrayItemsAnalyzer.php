<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
final class ArrayItemsAnalyzer
{
    public function hasArrayExclusiveDefinedVariableNames(Array_ $array, Scope $scope) : bool
    {
        foreach ($array->items as $arrayItem) {
            $variableName = $this->resolveStringValue($arrayItem);
            if ($variableName === null) {
                continue;
            }
            // the variable must not be defined here
            if ($scope->hasVariableType($variableName)->no()) {
                return \false;
            }
        }
        return \true;
    }
    public function hasArrayExclusiveUndefinedVariableNames(Array_ $array, Scope $scope) : bool
    {
        foreach ($array->items as $arrayItem) {
            $variableName = $this->resolveStringValue($arrayItem);
            if ($variableName === null) {
                continue;
            }
            // the variable must not be defined here
            if ($scope->hasVariableType($variableName)->yes()) {
                return \false;
            }
        }
        return \true;
    }
    private function resolveStringValue(?ArrayItem $arrayItem) : ?string
    {
        if (!$arrayItem instanceof ArrayItem) {
            return null;
        }
        if (!$arrayItem->value instanceof String_) {
            return null;
        }
        return $arrayItem->value->value;
    }
}
