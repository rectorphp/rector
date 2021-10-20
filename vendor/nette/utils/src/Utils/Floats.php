<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Utils;

use RectorPrefix20211020\Nette;
/**
 * Floating-point numbers comparison.
 */
class Floats
{
    use Nette\StaticClass;
    private const EPSILON = 1.0E-10;
    /**
     * @param float $value
     */
    public static function isZero($value) : bool
    {
        return \abs($value) < self::EPSILON;
    }
    /**
     * @param float $value
     */
    public static function isInteger($value) : bool
    {
        return \abs(\round($value) - $value) < self::EPSILON;
    }
    /**
     * Compare two floats. If $a < $b it returns -1, if they are equal it returns 0 and if $a > $b it returns 1
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     */
    public static function compare($a, $b) : int
    {
        if (\is_nan($a) || \is_nan($b)) {
            throw new \LogicException('Trying to compare NAN');
        } elseif (!\is_finite($a) && !\is_finite($b) && $a === $b) {
            return 0;
        }
        $diff = \abs($a - $b);
        if ($diff < self::EPSILON || $diff / \max(\abs($a), \abs($b)) < self::EPSILON) {
            return 0;
        }
        return $a < $b ? -1 : 1;
    }
    /**
     * Returns true if $a = $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     */
    public static function areEqual($a, $b) : bool
    {
        return self::compare($a, $b) === 0;
    }
    /**
     * Returns true if $a < $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     */
    public static function isLessThan($a, $b) : bool
    {
        return self::compare($a, $b) < 0;
    }
    /**
     * Returns true if $a <= $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     */
    public static function isLessThanOrEqualTo($a, $b) : bool
    {
        return self::compare($a, $b) <= 0;
    }
    /**
     * Returns true if $a > $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     */
    public static function isGreaterThan($a, $b) : bool
    {
        return self::compare($a, $b) > 0;
    }
    /**
     * Returns true if $a >= $b
     * @throws \LogicException if one of parameters is NAN
     * @param float $a
     * @param float $b
     */
    public static function isGreaterThanOrEqualTo($a, $b) : bool
    {
        return self::compare($a, $b) >= 0;
    }
}
