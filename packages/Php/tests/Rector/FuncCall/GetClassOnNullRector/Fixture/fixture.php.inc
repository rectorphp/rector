<?php

namespace Rector\Php\Tests\Rector\FuncCall\GetClassOnNullRector\Fixture;

final class SomeClass
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

namespace Rector\Php\Tests\Rector\FuncCall\GetClassOnNullRector\Fixture;

final class SomeClass
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
