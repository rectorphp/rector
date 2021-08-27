<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

class SomeClass
{
    public function hello(string $world = 'world') {
        printf('Hello %s', $world);
    }
}

class SomeOtherClassUsingAnAnonymousClass
{
    public function doSomething(): void
    {
        $class = new class () extends SomeClass {
            public function hello(string $world = 'world') {
                printf('Hi %s', $world);
            }
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

class SomeClass
{
    /**
     * @param string $world
     */
    public function hello($world = 'world') {
        printf('Hello %s', $world);
    }
}

class SomeOtherClassUsingAnAnonymousClass
{
    public function doSomething(): void
    {
        $class = new class () extends SomeClass {
            /**
             * @param string $world
             */
            public function hello($world = 'world') {
                printf('Hi %s', $world);
            }
        };
    }
}

?>
