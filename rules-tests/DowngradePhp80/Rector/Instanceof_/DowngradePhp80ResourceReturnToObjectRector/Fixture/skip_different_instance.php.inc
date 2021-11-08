<?php

namespace Rector\Tests\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector\Fixture;

class SkipDifferentInstance
{
    public function run($obj)
    {
        $obj instanceof \stdClass;
    }
}
