<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class NewClass extends stdClass
{
    public function run(array $items)
    {
        return new SomeClass(...$items);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class NewClass extends stdClass
{
    public function run(array $items)
    {
        return (new \ReflectionClass(SomeClass::class))->newInstanceArgs($items);
    }
}

?>
