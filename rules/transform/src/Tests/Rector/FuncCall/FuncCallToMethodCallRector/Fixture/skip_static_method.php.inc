<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

class SkipStaticMethod
{
    public static function go()
    {
        $value = config('value');
    }
}
