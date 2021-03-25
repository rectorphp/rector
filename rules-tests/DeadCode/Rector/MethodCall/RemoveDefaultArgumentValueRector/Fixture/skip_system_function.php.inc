<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveDefaultArgumentValueRector\Fixture;

class SkipNoArgs
{
    public function run()
    {
        trigger_error('Error message', E_USER_NOTICE);
    }
}
