<?php

namespace RectorPrefix202604;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (\PHP_VERSION_ID < 80400) {
    class RoundingMode
    {
        public const HalfAwayFromZero = 'halfawayfromzero';
        public const HalfTowardsZero = 'halftowardszero';
        public const HalfEven = 'halfeven';
        public const HalfOdd = 'halfodd';
        public const TowardsZero = 'towardszero';
        public const AwayFromZero = 'awayfromzero';
        public const NegativeInfinity = 'negativeinfinity';
        public const PositiveInfinity = 'positiveinfinity';
    }
    \class_alias('RectorPrefix202604\RoundingMode', 'RoundingMode', \false);
}
