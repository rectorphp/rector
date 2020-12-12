<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\SomeInterface;

class SkipUsingInterface
{
    public function execute(SomeInterface $obj)
    {
        $obj->run();
    }
}

?>
