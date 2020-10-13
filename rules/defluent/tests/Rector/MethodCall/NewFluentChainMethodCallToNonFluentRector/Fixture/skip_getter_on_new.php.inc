<?php

namespace Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\DifferentReturnValues;

class SkipGetterOnNew
{
    public function run()
    {
        $differentReturnValues = (new DifferentReturnValues())->otherFunction();
    }
}
