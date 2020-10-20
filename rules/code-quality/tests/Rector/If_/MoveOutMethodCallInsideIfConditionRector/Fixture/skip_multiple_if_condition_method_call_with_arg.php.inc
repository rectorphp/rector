<?php

namespace Rector\Php\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionMethodCallWithArgWithMultipleCall
{
    public function run($arg)
    {
        $obj = new self();

        if ($obj->condition($arg->getData())) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
