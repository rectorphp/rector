<?php

namespace Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\Fixture;

use ReflectionNamedType;

final class SkipInstanceOf
{
    public function run(\ReflectionProperty $reflectionProperty)
    {
        if ($reflectionProperty->getType() instanceof ReflectionNamedType) {
            return true;
        }

        return false;
    }
}
