<?php

namespace Rector\Tests\Transform\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class SkipStaticMethod
{
    public static function go()
    {
        $value = config('value');
    }
}
