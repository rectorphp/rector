<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run(array $items, array $otherItems)
    {
        some_function(...$items, ...$otherItems);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run(array $items, array $otherItems)
    {
        call_user_func_array('some_function', array_merge($items, $otherItems));
    }
}

?>
