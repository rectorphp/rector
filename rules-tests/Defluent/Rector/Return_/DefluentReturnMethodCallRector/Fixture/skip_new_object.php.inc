<?php

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source\SelfStaticThisButNewVersion;

final class SkipNewObject
{
    public function run(SelfStaticThisButNewVersion $selfButNewVersion)
    {
        return $selfButNewVersion->duplicateSelf();
    }

    public function run2(SelfStaticThisButNewVersion $selfButNewVersion)
    {
        return $selfButNewVersion->duplicateStatic();
    }

    public function run3(SelfStaticThisButNewVersion $selfButNewVersion)
    {
        return $selfButNewVersion->duplicateThis();
    }
}
