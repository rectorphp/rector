<?php

namespace Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class SkipIfConditionMethodCallWithArgWithVariableExistsInParam
{
    public function run($arg, $objCondition)
    {
        $obj = new self();
        if ($obj->condition($arg + 1) === 1) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
