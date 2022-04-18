<?php

namespace RectorPrefix20220418\MyCLabs\Enum\PHPUnit;

use RectorPrefix20220418\MyCLabs\Enum\Enum;
use RectorPrefix20220418\SebastianBergmann\Comparator\ComparisonFailure;
/**
 * Use this Comparator to get nice output when using PHPUnit assertEquals() with Enums.
 *
 * Add this to your PHPUnit bootstrap PHP file:
 *
 * \SebastianBergmann\Comparator\Factory::getInstance()->register(new \MyCLabs\Enum\PHPUnit\Comparator());
 */
final class Comparator extends \RectorPrefix20220418\SebastianBergmann\Comparator\Comparator
{
    public function accepts($expected, $actual)
    {
        return $expected instanceof \RectorPrefix20220418\MyCLabs\Enum\Enum && ($actual instanceof \RectorPrefix20220418\MyCLabs\Enum\Enum || $actual === null);
    }
    /**
     * @param Enum $expected
     * @param Enum|null $actual
     *
     * @return void
     */
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = \false, $ignoreCase = \false)
    {
        if ($expected->equals($actual)) {
            return;
        }
        throw new \RectorPrefix20220418\SebastianBergmann\Comparator\ComparisonFailure($expected, $actual, $this->formatEnum($expected), $this->formatEnum($actual), \false, 'Failed asserting that two Enums are equal.');
    }
    private function formatEnum(\RectorPrefix20220418\MyCLabs\Enum\Enum $enum = null)
    {
        if ($enum === null) {
            return "null";
        }
        return \get_class($enum) . "::{$enum->getKey()}()";
    }
}
