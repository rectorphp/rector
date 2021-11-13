<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run()
    {
        var_dump(func_get_args());
    }

    public function call(array $items)
    {
        $method = 'run';
        $this->$method(...$items);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run()
    {
        var_dump(func_get_args());
    }

    public function call(array $items)
    {
        $method = 'run';
        call_user_func_array([$this, $method], $items);
    }
}

?>
