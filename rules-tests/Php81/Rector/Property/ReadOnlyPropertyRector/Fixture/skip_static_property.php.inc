<?php

use stdClass;

final class SkipStaticProperty
{
    private static stdClass $x;

    public function __construct(stdClass $x)
    {
        self::$x = $x;
    }

    public static function x(): stdClass
    {
        return self::$x;
    }
}
