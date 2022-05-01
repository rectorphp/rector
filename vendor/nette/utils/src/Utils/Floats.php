<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220501\Nette\Utils;

use RectorPrefix20220501\Nette;
/**
 * Floating-point numbers comparison.
 */
class Floats
{
    use Nette\StaticClass;
    private const EPSILON = 1.0E-10;
    public static function isZero(float $value) : bool
    {
        return \abs($value) < self::EPSILON;
    }
    public static function isInteger(float $value) : bool
    {
        return \abs(\round($value) - $value) < self::EPSILON;
    }
    /**
     * Compare two floats. If $a < $b it returns -1, if they are equal it returns 0 and if $a > $b it returns 1
     * @throws \LogicException if one of parameters is NAN
     */
    public static function compare(float $a, float $b) : int
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
     */
    public static function areEqual(float $a, float $b) : bool
    {
        return self::compare($a, $b) === 0;
    }
    /**
     * Returns true if $a < $b
     * @throws \LogicException if one of parameters is NAN
     */
    public static function isLessThan(float $a, float $b) : bool
    {
        return self::compare($a, $b) < 0;
    }
    /**
     * Returns true if $a <= $b
     * @throws \LogicException if one of parameters is NAN
     */
    public static function isLessThanOrEqualTo(float $a, float $b) : bool
    {
        return self::compare($a, $b) <= 0;
    }
    /**
     * Returns true if $a > $b
     * @throws \LogicException if one of parameters is NAN
     */
    public static function isGreaterThan(float $a, float $b) : bool
    {
        return self::compare($a, $b) > 0;
    }
    /**
     * Returns true if $a >= $b
     * @throws \LogicException if one of parameters is NAN
     */
    public static function isGreaterThanOrEqualTo(float $a, float $b) : bool
    {
        return self::compare($a, $b) >= 0;
    }
}
