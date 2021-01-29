<?php

namespace Rector\Defluent\Tests\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\DefluentReturnMethodCallRector\Source\SelfButNewVersion;

final class SkipNewObject
{
    public function run(SelfButNewVersion $selfButNewVersion)
    {
        return $selfButNewVersion->duplicate();
    }
}
