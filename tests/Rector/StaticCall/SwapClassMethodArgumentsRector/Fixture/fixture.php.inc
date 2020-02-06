<?php

namespace Rector\Core\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\Fixture;

class SomeClass
{
    public static function run($first, $second)
    {
        self::run($first, $second);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\StaticCall\SwapClassMethodArgumentsRector\Fixture;

class SomeClass
{
    public static function run($second, $first)
    {
        self::run($second, $first);
    }
}

?>
