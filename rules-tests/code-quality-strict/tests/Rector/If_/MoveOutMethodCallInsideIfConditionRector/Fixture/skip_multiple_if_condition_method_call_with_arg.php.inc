<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class SkipMultipleIfConditionMethodCallWithArg
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
