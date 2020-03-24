<?php

namespace Rector\Php72\Tests\Rector\FuncCall\GetClassOnNullRector\Fixture;

final class TrickyCases
{
    public function getItem()
    {
        $getClass = $value !== null ? get_class($value) : self::class;
        $getClass = null !== $value ? get_class($value) : self::class;

        $getClass = $value !== null ? get_class($value) : false;

        $getClass = $value === '123' ? get_class($value) : self::class;
        $getClass = '123' === $value ? get_class($value) : self::class;

        // change this
        $getClass = $value === null ? get_class($value) : self::class;
    }
}

?>
-----
<?php

namespace Rector\Php72\Tests\Rector\FuncCall\GetClassOnNullRector\Fixture;

final class TrickyCases
{
    public function getItem()
    {
        $getClass = $value !== null ? get_class($value) : self::class;
        $getClass = null !== $value ? get_class($value) : self::class;

        $getClass = $value !== null ? get_class($value) : false;

        $getClass = $value === '123' ? get_class($value) : self::class;
        $getClass = '123' === $value ? get_class($value) : self::class;

        // change this
        $getClass = $value === null ? $value !== null ? get_class($value) : self::class : self::class;
    }
}

?>
