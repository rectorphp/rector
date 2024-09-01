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
namespace RectorPrefix202409\Fidry\CpuCoreCounter\Finder;

use function file_get_contents;
use function is_file;
use function sprintf;
use function substr_count;
use const PHP_EOL;
/**
 * Find the number of CPU cores looking up at the cpuinfo file which is available
 * on Linux systems and Windows systems with a Linux sub-system.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L903-L909
 * @see https://unix.stackexchange.com/questions/146051/number-of-processors-in-proc-cpuinfo
 */
final class CpuInfoFinder implements CpuCoreFinder
{
    private const CPU_INFO_PATH = '/proc/cpuinfo';
    public function diagnose() : string
    {
        if (!is_file(self::CPU_INFO_PATH)) {
            return sprintf('The file "%s" could not be found.', self::CPU_INFO_PATH);
        }
        $cpuInfo = file_get_contents(self::CPU_INFO_PATH);
        if (\false === $cpuInfo) {
            return sprintf('Could not get the content of the file "%s".', self::CPU_INFO_PATH);
        }
        return sprintf('Found the file "%s" with the content:%s%s%sWill return "%s".', self::CPU_INFO_PATH, PHP_EOL, $cpuInfo, PHP_EOL, self::countCpuCores($cpuInfo));
    }
    /**
     * @return positive-int|null
     */
    public function find() : ?int
    {
        $cpuInfo = self::getCpuInfo();
        return null === $cpuInfo ? null : self::countCpuCores($cpuInfo);
    }
    public function toString() : string
    {
        return 'CpuInfoFinder';
    }
    private static function getCpuInfo() : ?string
    {
        if (!@is_file(self::CPU_INFO_PATH)) {
            return null;
        }
        $cpuInfo = @file_get_contents(self::CPU_INFO_PATH);
        return \false === $cpuInfo ? null : $cpuInfo;
    }
    /**
     * @internal
     *
     * @return positive-int|null
     */
    public static function countCpuCores(string $cpuInfo) : ?int
    {
        $processorCount = substr_count($cpuInfo, 'processor');
        return $processorCount > 0 ? $processorCount : null;
    }
}
