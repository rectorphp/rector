<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\ObjectWithMagicCallsNoParam;

final class SkipNoMethodNoParamOnAssign
{
    public function run(ObjectWithMagicCallsNoParam $obj)
    {
        $obj->name = 20;
    }
}
