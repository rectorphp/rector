<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use function Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\some_view_function;

final class SkipStaticMethod
{
    public static function run()
    {
        $result = \translate('name');
    }
}
