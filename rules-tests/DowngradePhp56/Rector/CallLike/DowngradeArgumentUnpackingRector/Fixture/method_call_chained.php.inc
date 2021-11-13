<?php

namespace Rector\Tests\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector\Fixture;

final class SomeClass
{
    public function run()
    {
        var_dump(func_get_args());
    }

    public function chain()
    {
        return $this;
    }

    public function call(array $items)
    {
        $this->chain(...$items)->run(...$items);
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

    public function chain()
    {
        return $this;
    }

    public function call(array $items)
    {
        call_user_func_array([call_user_func_array([$this, 'chain'], $items), 'run'], $items);
    }
}

?>
