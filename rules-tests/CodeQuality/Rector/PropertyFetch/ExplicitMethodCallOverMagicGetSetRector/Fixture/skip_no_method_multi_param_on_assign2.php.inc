<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\ObjectWithMagicCallsMultiParam;

final class SkipNoMethodOnMultiParamOnAssign2
{
    public function run()
    {
        $obj = new ObjectWithMagicCallsMultiParam();
        $obj->name = 20;
    }
}
