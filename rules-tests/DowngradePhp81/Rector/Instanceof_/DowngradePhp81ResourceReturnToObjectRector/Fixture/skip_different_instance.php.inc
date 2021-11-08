<?php

namespace Rector\Tests\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector\Fixture;

class SkipDifferentInstance
{
    public function run($obj)
    {
        $obj instanceof \stdClass;
    }
}
