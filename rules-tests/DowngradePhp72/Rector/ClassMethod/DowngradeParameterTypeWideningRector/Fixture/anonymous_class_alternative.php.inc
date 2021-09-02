<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SomeInterface
{
    public function hello(string $world = 'world');
}

class SomeClass2
{
    public function doSomething(): void
    {
        $class = new class (function () {
            return $this->doSomethingElse();
        }) implements SomeInterface {
            public function hello(string $world = 'world') {
                printf('Hi %s', $world);
            }
        };
    }

    public function doSomethingElse(): void
    {
        print('Hello again');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface SomeInterface
{
    /**
     * @param string $world
     */
    public function hello($world = 'world');
}

class SomeClass2
{
    public function doSomething(): void
    {
        $class = new class (function () {
            return $this->doSomethingElse();
        }) implements SomeInterface {
            /**
             * @param string $world
             */
            public function hello($world = 'world') {
                printf('Hi %s', $world);
            }
        };
    }

    public function doSomethingElse(): void
    {
        print('Hello again');
    }
}

?>
