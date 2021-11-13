<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run(array $items)
    {
        some_function('foo', 'bar', ...$items, 'baz');
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
        call_user_func_array('some_function', array_merge(['foo', 'bar'], $items, ['baz']));
    }
}

?>
