<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\Source\SomeClassHasGetDefaultValue;

class SkipDifferentObject
{
    public function run(SomeClassHasGetDefaultValue $someClass)
    {
        return $someClass->getDefaultValue();
    }
}
