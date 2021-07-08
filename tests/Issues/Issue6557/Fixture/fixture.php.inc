<?php

namespace Rector\Core\Tests\Issues\Issue6557\Fixture;

final class Fixture
{
    public function run()
    {
        return function () {
            $demo = new SomeClass();
            $demo->doSomething();
            return;
        };
    }
}

final class SomeClass
{
    public function doSomething(): void
    {

    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6557\Fixture;

final class Fixture
{
    public function run()
    {
        return function () {
            $someClass = new SomeClass();
            $someClass->doSomething();
            return;
        };
    }
}

final class SomeClass
{
    public function doSomething(): void
    {

    }
}

?>
