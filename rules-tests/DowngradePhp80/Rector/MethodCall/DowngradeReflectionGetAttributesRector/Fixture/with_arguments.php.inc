<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\Fixture;

final class WithArguments
{
    public function run(\ReflectionClass $reflectionClass)
    {
        if ($reflectionClass->getAttributes('someName', 1)) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionGetAttributesRector\Fixture;

final class WithArguments
{
    public function run(\ReflectionClass $reflectionClass)
    {
        if (method_exists($reflectionClass, 'getAttributes') ? $reflectionClass->getAttributes('someName', 1) : []) {
            return true;
        }

        return false;
    }
}

?>
