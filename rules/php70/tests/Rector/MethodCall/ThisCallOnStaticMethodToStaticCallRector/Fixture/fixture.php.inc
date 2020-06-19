<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

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

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class SomeClass
{
    public static function run()
    {
        static::eat('something');
    }

    public static function eat(string $thing)
    {
    }
}

?>
