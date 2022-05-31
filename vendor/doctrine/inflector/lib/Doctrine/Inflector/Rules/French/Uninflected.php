<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Doctrine\Inflector\Rules\French;

use RectorPrefix20220531\Doctrine\Inflector\Rules\Pattern;
final class Uninflected
{
    /**
     * @return Pattern[]
     */
    public static function getSingular() : iterable
    {
        yield from self::getDefault();
    }
    /**
     * @return Pattern[]
     */
    public static function getPlural() : iterable
    {
        yield from self::getDefault();
    }
    /**
     * @return Pattern[]
     */
    private static function getDefault() : iterable
    {
        (yield new \RectorPrefix20220531\Doctrine\Inflector\Rules\Pattern(''));
    }
}
