<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

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

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class NullableArray
{
    public function number(?array $items)
    {
        return count((array) $items);
    }
}

?>
