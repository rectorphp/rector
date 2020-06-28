<?php

namespace Rector\Core\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\Core\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\DifferentReturnValues;

class SkipDifferentType
{
    public function run()
    {
        $differentReturnValues = new DifferentReturnValues();
        $differentReturnValues->someFunction()
            ->otherFunction();

        $differentReturnValues->otherFunction()
            ->someFunction();
    }
}
