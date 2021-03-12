<?php

namespace Rector\Tests\Removing\Rector\FuncCall\RemoveFuncCallRector\Fixture;

class SkipAssign
{
    public function run()
    {
        $getAssign = ini_get('safe_mode');

        $notGetAssign = ! ini_get('safe_mode');
    }
}
