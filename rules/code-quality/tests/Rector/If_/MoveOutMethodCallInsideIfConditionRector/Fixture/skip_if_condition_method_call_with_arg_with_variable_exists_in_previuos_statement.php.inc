<?php

namespace Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use stdClass;

class SkipIfConditionMethodCallWithArgWithVariableExistsInPreviousStatement
{
    public function run($arg, $objCondition)
    {
        $objCondition = new stdClass;

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
