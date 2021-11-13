<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass extends stdClass
{
    public function run(array $items)
    {
        return new self(...$items, 'foo');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass extends stdClass
{
    public function run(array $items)
    {
        return (new \ReflectionClass(get_class()))->newInstanceArgs(array_merge($items, ['foo']));
    }
}

?>
