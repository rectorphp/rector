<?php

namespace Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use function Rector\Generic\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\some_view_function;

function anothehFunction()
{
    some_view_function('template');
}
