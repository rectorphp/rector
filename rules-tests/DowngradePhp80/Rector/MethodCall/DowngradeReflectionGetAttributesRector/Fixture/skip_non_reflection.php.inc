<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\Source\NonReflectionGetAttributes;

final class SkipNonReflection
{
    public function run(NonReflectionGetAttributes $nonReflectionGetAttributes)
    {
        if ($nonReflectionGetAttributes->getAttributes()) {
            return true;
        }

        return false;
    }
}
