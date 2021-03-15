<?php

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use function Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Source\some_view_function;

final class SkipStaticMethod
{
    public static function run()
    {
        $result = \translate('name');
    }
}
