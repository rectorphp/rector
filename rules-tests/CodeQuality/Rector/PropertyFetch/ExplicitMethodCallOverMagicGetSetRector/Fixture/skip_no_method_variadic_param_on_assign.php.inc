<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\ObjectWithMagicCallsVariadicParam;

final class SkipNoMethodVariadicParamOnAssign
{
    public function run(ObjectWithMagicCallsVariadicParam $obj)
    {
        $obj->name = 20;
    }
}
