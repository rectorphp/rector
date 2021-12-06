<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\Fixture;

class SomeClass
{
    public function run(\ReflectionClass $reflectionClass)
    {
        if ($reflectionClass->getAttributes()) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\Fixture;

class SomeClass
{
    public function run(\ReflectionClass $reflectionClass)
    {
        if ([]) {
            return true;
        }

        return false;
    }
}

?>
