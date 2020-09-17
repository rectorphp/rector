<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Source\FirstStaticClass;

class ConstructAlreadyThereDependency
{
    public function __construct(FirstStaticClass $firstStaticClass)
    {
        FirstStaticClass::someStaticFunction();
    }
}

?>
-----
<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Source\FirstStaticClass;

class ConstructAlreadyThereDependency
{
    public function __construct(FirstStaticClass $firstStaticClass)
    {
        $firstStaticClass->someStaticFunction();
    }
}

?>
