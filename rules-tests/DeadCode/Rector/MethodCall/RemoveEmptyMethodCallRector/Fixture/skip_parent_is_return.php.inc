<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

function run()
{
    $obj = new EmptyMethod();
    return $obj->run();
}
