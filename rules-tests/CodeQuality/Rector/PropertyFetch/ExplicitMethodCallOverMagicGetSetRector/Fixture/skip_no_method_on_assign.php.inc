<?php

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Fixture;

use Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source\ObjectWithMagicCalls;

final class SkipNoMethodOnAssign
{
    public function run(ObjectWithMagicCalls $magicObject)
    {
        $magicObject->age = 20;
    }
}
