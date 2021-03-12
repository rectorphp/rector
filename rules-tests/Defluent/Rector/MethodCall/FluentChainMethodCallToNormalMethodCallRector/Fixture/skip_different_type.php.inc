<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class SkipDifferentType
{
    public function run()
    {
        $differentReturnValues = new DifferentReturnValues();
        $differentReturnValues->otherFunction()
            ->someFunction();
    }
}
