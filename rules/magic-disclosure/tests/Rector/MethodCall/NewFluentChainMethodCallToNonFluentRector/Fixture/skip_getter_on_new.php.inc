<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\Source\DifferentReturnValues;

class SkipGetterOnNew
{
    public function run()
    {
        $differentReturnValues = (new DifferentReturnValues())->otherFunction();
    }
}
