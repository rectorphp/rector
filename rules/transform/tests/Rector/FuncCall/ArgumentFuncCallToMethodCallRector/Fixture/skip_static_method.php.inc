<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class SkipStaticMethod
{
    public static function go()
    {
        $value = config('value');
    }
}
