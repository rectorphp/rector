<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class SkipCallFromCurrentThisObject
{
    public function run()
    {
        $this->init();

        echo 'run';
    }

    // can be overridden by child
    protected function init()
    {
    }
}
