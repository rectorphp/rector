<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

class SomeClass2
{
    private function hello(string $world = 'world') {
        printf('Hello %s', $world);
    }
}

class SkipAnonymousClassPrivateMethod
{
    public function doSomething(): void
    {
        $class = new class () extends SomeClass2 {
            private function hello(string $world = 'world') {
                printf('Hi %s', $world);
            }
        };
    }
}

?>
