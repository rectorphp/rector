<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

use Rector\Core\Testing\Contract\RunnableInterface;

class SomeParent
{
    public function speak()
    {
        return $this->whoami();
    }
    public static function whoami()
    {
        return "parent";
    }
}

class SomeChild extends SomeParent
{
    public static function whoami()
    {
        return "child";
    }
}

class SomeRunnable implements RunnableInterface
{
    public function run()
    {
        $expectedString = "child";
        $c = new SomeChild();
        return $c->speak() === $expectedString;
    }
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

use use Rector\Core\Testing\Contract\RunnableInterface;

class SomeParent
{
    public function speak()
    {
        return static::whoami();
    }
    public static function whoami()
    {
        return "parent";
    }
}

class SomeChild extends SomeParent
{
    public static function whoami()
    {
        return "child";
    }
}

class SomeRunnable implements RunnableInterface
{
    public function run()
    {
        $expectedString = "child";
        $c = new SomeChild();
        return $c->speak();
    }
}

?>
