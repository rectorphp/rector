<?php

namespace Rector\Php\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class IfConditionWithMethodCallInside
{
    public function run($arg1, $arg2)
    {
        $obj = new self();

        if ($obj->condition($arg1) === $obj->condition($arg2)) {

        }
    }

    public function condition($arg): int
    {
        return 1;
    }
}

?>
