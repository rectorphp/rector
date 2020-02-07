<?php

namespace Rector\DeadCode\Tests\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

trait SkipTrait
{
    public function run()
    {
        parent::run();
    }
}
