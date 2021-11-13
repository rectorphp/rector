<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public static function run()
    {
        var_dump(func_get_args());
    }

    public function call(array $items)
    {
        self::run(...$items);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public static function run()
    {
        var_dump(func_get_args());
    }

    public function call(array $items)
    {
        call_user_func_array(['self', 'run'], $items);
    }
}

?>
