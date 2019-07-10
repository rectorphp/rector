<?php

namespace Rector\Php\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class SomeClass
{
    public static function run()
    {
        $this->eat('something');
    }

    public static function eat(string $thing)
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
        self::eat('something');
    }

    public static function eat(string $thing)
    {
    }
}

?>
