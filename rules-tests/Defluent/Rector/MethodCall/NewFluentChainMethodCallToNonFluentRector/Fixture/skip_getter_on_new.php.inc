<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\DifferentReturnValues;

class SkipGetterOnNew
{
    public function run()
    {
        $differentReturnValues = (new DifferentReturnValues())->otherFunction();
    }
}
