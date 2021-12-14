<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\Fixture;

use ReflectionProperty;

class SkipDifferentMethodName
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        return $reflectionProperty->getName();
    }
}
