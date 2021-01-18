<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass;

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

namespace Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass;

class AnotherClass
{
    public function __construct(private \Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass $firstStaticClass)
    {
    }
    public function run()
    {
        FirstStaticClass::someStaticFunction();
    }
}

?>
