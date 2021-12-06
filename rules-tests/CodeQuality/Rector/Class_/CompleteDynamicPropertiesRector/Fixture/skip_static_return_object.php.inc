<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\Source\StaticFactory;

final class SkipStaticReturnObject
{
    public function getCurrentTimestamp()
    {
        return StaticFactory::now()->timestamp;
    }
}
