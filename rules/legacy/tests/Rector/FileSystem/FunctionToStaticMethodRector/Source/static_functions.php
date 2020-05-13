<?php

declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\FileSystem\FunctionToStaticMethodRector\Source;

function first_static_function()
{
    return 5;
}
$value = first_static_function();
