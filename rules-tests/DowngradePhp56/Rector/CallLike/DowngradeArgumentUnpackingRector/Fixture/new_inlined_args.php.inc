<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class NewInlinedArgs extends stdClass
{
    public function run(mixed $item)
    {
        return new SomeClass(...['foo', $item]);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class NewInlinedArgs extends stdClass
{
    public function run(mixed $item)
    {
        return new SomeClass('foo', $item);
    }
}

?>
