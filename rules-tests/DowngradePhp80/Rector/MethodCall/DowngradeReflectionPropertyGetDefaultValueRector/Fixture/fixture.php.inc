<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\Fixture;

use ReflectionProperty;

class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        return $reflectionProperty->getDefaultValue();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\Fixture;

use ReflectionProperty;

class SomeClass
{
    public function run(ReflectionProperty $reflectionProperty)
    {
        return $reflectionProperty->getDeclaringClass()->getDefaultProperties()[$reflectionProperty->getName()] ?? null;
    }
}

?>
