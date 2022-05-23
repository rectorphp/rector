<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipPropertyArrayFilledByConstructViaMethodCall2
{
    /** @var array */
    private static $property;

    public function __construct()
    {
        self::fill();
    }

    private static function fill()
    {
        self::$property = [];
    }

    public static function run(): int
    {
        return count(self::$property);
    }
}
