<?php

namespace Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionMethodCallWithArgWithBoolReturn
{
    public function run($arg)
    {
        $obj = new self();

        if ($obj->condition($arg)) {

        }
    }

    public function condition($arg): bool
    {

    }
}

?>
