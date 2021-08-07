<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

class SkipInOtherMethodCall
{
    public function run()
    {
        $obj = new EmptyMethod();
        $this->assertNull($obj->run());
    }

    private function assertNull($value)
    {
        return $value === null;
    }
}

?>
