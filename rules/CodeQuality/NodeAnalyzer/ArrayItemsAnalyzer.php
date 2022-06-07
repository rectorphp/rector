<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
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
