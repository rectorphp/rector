<?php

namespace Rector\CodeQuality\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

class SkipFromThis
{
    public function run($arg)
    {
        if ($this->condition($arg)) {

        }
    }

    public function condition($arg)
    {

    }
}
