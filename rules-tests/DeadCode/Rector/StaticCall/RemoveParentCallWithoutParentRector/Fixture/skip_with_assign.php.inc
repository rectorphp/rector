<?php

namespace Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\Fixture;

class SkipWithAssign
{
    public function run()
    {
        $result = parent::run();

        return $result;
    }
}

?>