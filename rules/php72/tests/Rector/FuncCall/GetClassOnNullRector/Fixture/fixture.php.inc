<?php

namespace Rector\Php72\Tests\Rector\FuncCall\GetClassOnNullRector\Fixture;

final class Fixture
{
    public function getItem()
    {
        $value = null;
        $getClass = get_class($value);

        /** @var null|string $value */
        $value = '...';
        $getClass = get_class($value);

        $value = '...';
        $getClass = get_class($value);
    }
}

function getClassOnNullOutsideClass()
{
    get_class(null);
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\FuncCall\GetClassOnNullRector\Fixture;

final class Fixture
{
    public function getItem()
    {
        $value = null;
        $getClass = $value !== null ? get_class($value) : self::class;

        /** @var null|string $value */
        $value = '...';
        $getClass = $value !== null ? get_class($value) : self::class;

        $value = '...';
        $getClass = get_class($value);
    }
}

function getClassOnNullOutsideClass()
{
    get_class(null);
}

?>
