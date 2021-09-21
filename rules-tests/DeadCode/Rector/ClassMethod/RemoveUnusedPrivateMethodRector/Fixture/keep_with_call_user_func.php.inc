<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class KeepWithCallUserFunc
{
    public function go($unknown)
    {
        call_user_func([$this, $unknown]);
    }

    private function called()
    {
    }
}
