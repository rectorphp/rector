<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\Source\Foo;

$reflectionClass = new \ReflectionClass(Foo::class);
$constants = $reflectionClass->getConstants(SOMEOTHERCLASS::IS_PUBLIC);
