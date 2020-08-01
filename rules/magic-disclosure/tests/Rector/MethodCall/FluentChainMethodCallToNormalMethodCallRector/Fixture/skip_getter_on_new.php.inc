<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\DifferentReturnValues;

class SkipGetterOnNew
{
    public function run()
    {
        $differentReturnValues = (new DifferentReturnValues())->otherFunction();
    }
}
