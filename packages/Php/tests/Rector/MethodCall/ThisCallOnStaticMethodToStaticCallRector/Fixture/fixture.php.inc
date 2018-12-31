<?php

namespace Rector\Php\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class SomeClass
{
    public static function run()
    {
        $this->eat();
    }

    public static function eat()
    {
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class SomeClass
{
    public static function run()
    {
        self::eat();
    }

    public static function eat()
    {
    }
}

?>
