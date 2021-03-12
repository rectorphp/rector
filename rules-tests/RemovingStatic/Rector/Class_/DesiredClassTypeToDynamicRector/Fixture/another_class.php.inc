<?php

namespace Rector\Tests\RemovingStatic\Rector\Class_\DesiredClassTypeToDynamicRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass;

class AnotherClass
{
    public function run()
    {
        FirstStaticClass::someStaticFunction();
    }
}

?>
-----
<?php

namespace Rector\Tests\RemovingStatic\Rector\Class_\DesiredClassTypeToDynamicRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass;

class AnotherClass
{
    public function __construct(private \Rector\Tests\RemovingStatic\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass $firstStaticClass)
    {
    }
    public function run()
    {
        FirstStaticClass::someStaticFunction();
    }
}

?>
