<?php

namespace Rector\Transform\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SkipStaticMethod
{
    public static function go()
    {
        $value = config('value');
    }
}
