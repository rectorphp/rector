<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run(array $items)
    {
        some_function(...$items);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run(array $items)
    {
        call_user_func_array('some_function', $items);
    }
}

?>
