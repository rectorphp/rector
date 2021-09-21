<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class KeepWithCallUserFunc2
{
    public function go($unknown)
    {
        call_user_func([$this, 'called']);
    }

    private function called()
    {
    }
}
