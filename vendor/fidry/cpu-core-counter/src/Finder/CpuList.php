<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace RectorPrefix202609\Fidry\CpuCoreCounter\Finder;

use function explode;
use function preg_match;
/**
 * @internal
 *
 * @see https://man7.org/linux/man-pages/man7/cpuset.7.html (FORMATS)
 */
final class CpuList
{
    private const CPU_RANGE_REGEX = '/^(?<first>\d+)(?:-(?<last>\d+))?$/';
    /**
     * @param string $cpuList E.g. "0-1,4" for the CPUs 0, 1 and 4.
     *
     * @return positive-int|null
     */
    public static function count(string $cpuList): ?int
    {
        $count = 0;
        foreach (explode(',', $cpuList) as $item) {
            if (1 !== preg_match(self::CPU_RANGE_REGEX, $item, $range)) {
                return null;
            }
            $first = (int) $range['first'];
            $last = isset($range['last']) ? (int) $range['last'] : $first;
            if ($last < $first) {
                return null;
            }
            $count += $last - $first + 1;
        }
        return $count > 0 ? $count : null;
    }
    private function __construct()
    {
    }
}
