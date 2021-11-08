<?php

namespace Rector\Tests\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector\Fixture;

class SkipCombineCheckWithIsResource
{
    public function run($image)
    {
        if (!$image instanceof \GdImage && !(\is_resource($image) && \get_resource_type($image) === 'gd')) {
        }
    }
}
