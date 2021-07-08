<?php

namespace Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\DefluentReturnMethodCallRector\Source\SelfButNewAssignVersion;

final class SkipNewObjectAssign
{
    public function run(SelfButNewAssignVersion $selfButNewAssignVersion)
    {
        return $selfButNewAssignVersion->withStatus(500);
    }
}
