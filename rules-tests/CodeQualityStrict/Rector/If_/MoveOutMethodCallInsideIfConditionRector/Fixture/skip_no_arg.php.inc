<?php

namespace Rector\Tests\CodeQualityStrict\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class SkipNoArg
{
    public function run()
    {
        $obj = new self();
        if ($obj->condition()) {

        }
    }

    public function condition()
    {

    }
}
