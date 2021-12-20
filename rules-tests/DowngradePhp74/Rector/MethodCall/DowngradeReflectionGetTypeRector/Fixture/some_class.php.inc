<?php

namespace Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\Fixture;

class SomeClass
{
    public function run(\ReflectionProperty $reflectionProperty)
    {
        if ($reflectionProperty->getType()) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector\Fixture;

class SomeClass
{
    public function run(\ReflectionProperty $reflectionProperty)
    {
        if (method_exists($reflectionProperty, 'getType') ? $reflectionProperty->getType() : null) {
            return true;
        }

        return false;
    }
}

?>
