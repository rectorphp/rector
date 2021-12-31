<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Doctrine\Inflector\Rules\Portuguese;

use RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern;
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
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('tórax'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('tênis'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('ônibus'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('lápis'));
        (yield new \RectorPrefix20211231\Doctrine\Inflector\Rules\Pattern('fênix'));
    }
}
