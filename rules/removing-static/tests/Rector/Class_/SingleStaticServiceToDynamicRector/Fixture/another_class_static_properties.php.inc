<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Source\ClassWithStaticProperties;

class AnotherClassStaticProperties
{
    public function run()
    {
        return ClassWithStaticProperties::$value;
    }
}

?>
-----
<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Source\ClassWithStaticProperties;

class AnotherClassStaticProperties
{
    private \Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Source\ClassWithStaticProperties $classWithStaticProperties;
    public function __construct(\Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Source\ClassWithStaticProperties $classWithStaticProperties)
    {
        $this->classWithStaticProperties = $classWithStaticProperties;
    }
    public function run()
    {
        return $this->classWithStaticProperties->value;
    }
}

?>
