<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

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

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

final class NullableArray
{
    public function number(?array $items)
    {
        return count((array) $items);
    }
}

?>
