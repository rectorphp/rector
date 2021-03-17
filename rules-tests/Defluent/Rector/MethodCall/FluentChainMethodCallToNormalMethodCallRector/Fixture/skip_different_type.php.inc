<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class SkipDifferentType
{
    public function run()
    {
        $differentReturnValues = new DifferentReturnValues();
        $differentReturnValues->otherFunction()
            ->someFunction();
    }
}
