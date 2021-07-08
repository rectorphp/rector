<?php

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source\SelfButCloneVersion;

final class SkipCloneObjectDirect
{
    public function run(SelfButCloneVersion $selfButCloneVersion)
    {
        return $selfButCloneVersion->duplicate();
    }
}
