<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use PHPStan\Analyser\Scope;
/**
 * @api used in downgrade
 */
final class VariableNaming
{
    /**
     * @api used in downgrade
     */
    public function createCountedValueName(string $valueName, ?Scope $scope) : string
    {
        if (!$scope instanceof Scope) {
            return $valueName;
        }
        // make sure variable name is unique
        if (!$scope->hasVariableType($valueName)->yes()) {
            return $valueName;
        }
        // we need to add number suffix until the variable is unique
        $i = 2;
        $countedValueNamePart = $valueName;
        while ($scope->hasVariableType($valueName)->yes()) {
            $valueName = $countedValueNamePart . $i;
            ++$i;
        }
        return $valueName;
    }
}
