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
namespace RectorPrefix202410\Fidry\CpuCoreCounter\Finder;

use function preg_match;
/**
 * Find the number of physical CPU cores for Windows.
 *
 * @see https://github.com/paratestphp/paratest/blob/c163539818fd96308ca8dc60f46088461e366ed4/src/Runners/PHPUnit/Options.php#L912-L916
 */
final class WmicPhysicalFinder extends ProcOpenBasedFinder
{
    private const CPU_CORE_COUNT_REGEX = '/NumberOfCores[\\s\\n]+(?<count>\\d+)/';
    protected function getCommand() : string
    {
        return 'wmic cpu get NumberOfCores';
    }
    public function toString() : string
    {
        return 'WmicPhysicalFinder';
    }
    protected function countCpuCores(string $process) : ?int
    {
        if (0 === preg_match(self::CPU_CORE_COUNT_REGEX, $process, $matches)) {
            return parent::countCpuCores($process);
        }
        $count = $matches['count'];
        return parent::countCpuCores($count);
    }
}
