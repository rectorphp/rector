<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class SkipWithAssignParentNotLoaded extends ParentNotLoaded
{
    public function run()
    {
        $result = parent::run();

        return $result;
    }
}
