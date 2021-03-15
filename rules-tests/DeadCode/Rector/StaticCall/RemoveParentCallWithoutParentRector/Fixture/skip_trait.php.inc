<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

trait SkipTrait
{
    public function run()
    {
        parent::run();
    }
}
