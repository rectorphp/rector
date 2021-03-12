<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\SomeInterface;

class C
{
    public function execute(SomeInterface $obj)
    {
        $obj->run();
    }
}

?>
