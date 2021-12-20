<?php

namespace Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\Fixture;

class SkipAlreadyHasMethodExists
{
    public function run(\ReflectionProperty $reflectionProperty)
    {
        if (method_exists($reflectionProperty, 'getType') ? $reflectionProperty->getType() : null) {
            return true;
        }

        return false;
    }
}
