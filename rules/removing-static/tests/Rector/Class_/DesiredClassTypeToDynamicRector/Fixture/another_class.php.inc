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
    private \Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass $firstStaticClass;
    public function __construct(\Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\Source\FirstStaticClass $firstStaticClass)
    {
        $this->firstStaticClass = $firstStaticClass;
    }
    public function run()
    {
        FirstStaticClass::someStaticFunction();
    }
}

?>
