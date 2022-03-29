<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class NewParent extends stdClass
{
    public function run(array $items)
    {
        return new parent(...$items);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class NewParent extends stdClass
{
    public function run(array $items)
    {
        return (new \ReflectionClass(get_parent_class()))->newInstanceArgs($items);
    }
}

?>
