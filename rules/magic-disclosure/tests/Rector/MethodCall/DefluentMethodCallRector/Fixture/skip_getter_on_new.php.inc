<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\DifferentReturnValues;

class SkipGetterOnNew
{
    public function run()
    {
        $differentReturnValues = (new DifferentReturnValues())->otherFunction();
    }
}
