<?php

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use function Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Source\some_view_function;

function anothehFunction()
{
    some_view_function('template');
}
