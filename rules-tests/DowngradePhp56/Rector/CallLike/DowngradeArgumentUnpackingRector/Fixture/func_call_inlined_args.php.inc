<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run(mixed $item)
    {
        some_function('foo', ...[$item, 'baz'], 'bar', 'qux');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run(mixed $item)
    {
        call_user_func_array('some_function', ['foo', $item, 'baz', 'bar', 'qux']);
    }
}

?>
