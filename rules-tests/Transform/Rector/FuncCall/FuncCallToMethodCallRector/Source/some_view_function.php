<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\FuncCall\FuncCallToMethodCallRector\Source;

if (function_exists('Rector\Tests\Removing\Rector\FuncCall\FuncCallToMethodCallRector\Source\some_view_function')) {
    return;
}

function some_view_function()
{

}
