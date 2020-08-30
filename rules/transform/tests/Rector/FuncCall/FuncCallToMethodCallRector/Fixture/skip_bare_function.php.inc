<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use function Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Source\some_view_function;

function anothehFunction()
{
    some_view_function('template');
}
