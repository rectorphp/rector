<?php

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source\SelfButCloneAssignVersion;

final class SkipCloneObjectAssign
{
    public function run(SelfButCloneAssignVersion $selfButCloneVersion)
    {
        return $selfButCloneVersion->withStatus(500);
    }
}
