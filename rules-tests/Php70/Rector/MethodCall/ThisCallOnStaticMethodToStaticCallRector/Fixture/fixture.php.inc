<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class SomeParent
{
    public function speak()
    {
        return $this->whoami();
    }
    public static function whoami()
    {
        return 'parent';
    }
}

class SomeChild extends SomeParent
{
    public static function whoami()
    {
        return 'child';
    }
}

class SomeRunnable
{
    public function run()
    {
        $someChild = new SomeChild();
        return $someChild->speak();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class SomeParent
{
    public function speak()
    {
        return static::whoami();
    }
    public static function whoami()
    {
        return 'parent';
    }
}

class SomeChild extends SomeParent
{
    public static function whoami()
    {
        return 'child';
    }
}

class SomeRunnable
{
    public function run()
    {
        $someChild = new SomeChild();
        return $someChild->speak();
    }
}

?>
