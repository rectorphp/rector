<?php

declare(strict_types=1);

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Source;

class ParentFiller
{
    public static function fill()
    {
        static::$property = [];
    }
}
