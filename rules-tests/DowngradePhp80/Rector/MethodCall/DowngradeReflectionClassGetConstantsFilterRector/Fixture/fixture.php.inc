<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\Source\Foo;

$reflectionClass = new \ReflectionClass(Foo::class);
$constants = $reflectionClass->getConstants(\ReflectionClassConstant::IS_PUBLIC);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\Source\Foo;

$reflectionClass = new \ReflectionClass(Foo::class);
$reflectionClassConstants = $reflectionClass->getReflectionConstants();
$result = [];
array_walk($reflectionClassConstants, function ($value) use (&$result) {
    if ($value->isPublic()) {
        $result[$value->getName()] = $value->getValue();
    }
});
$constants = $result;

?>
