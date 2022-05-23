<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Source\ParentFiller;

final class SkipPropertyArrayFilledByConstructViaMethodCall3 extends ParentFiller
{
    /** @var array */
    public static $property;

    public function __construct()
    {
        parent::fill();
    }

    public static function run(): int
    {
        return count(static::$property);
    }
}
