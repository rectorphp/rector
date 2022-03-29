<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class NewExpr extends stdClass
{
    public function run(array $items)
    {
        $class = 'SomeClass';
        return new $class(...$items);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class NewExpr extends stdClass
{
    public function run(array $items)
    {
        $class = 'SomeClass';
        return (new \ReflectionClass($class))->newInstanceArgs($items);
    }
}

?>
