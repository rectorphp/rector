<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class NullableArray
{
    public function number(?array $items)
    {
        return count($items);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class NullableArray
{
    public function number(?array $items)
    {
        return $items === null ? 0 : count($items);
    }
}

?>
