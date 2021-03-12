<?php

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source\SelfButNewVersion;

final class SkipNewObject
{
    public function run(SelfButNewVersion $selfButNewVersion)
    {
        return $selfButNewVersion->duplicate();
    }
}
