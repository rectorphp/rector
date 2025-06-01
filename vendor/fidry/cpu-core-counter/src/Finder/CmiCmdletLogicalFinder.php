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
namespace RectorPrefix202506\Fidry\CpuCoreCounter\Finder;

use function preg_match;
/**
 * Find the number of logical CPU cores for Windows leveraging the Get-CimInstance
 * cmdlet, which is a newer version that is recommended over Get-WmiObject.
 */
final class CmiCmdletLogicalFinder extends ProcOpenBasedFinder
{
    private const CPU_CORE_COUNT_REGEX = '/NumberOfLogicalProcessors[\\s\\n]-+[\\s\\n]+(?<count>\\d+)/';
    protected function getCommand() : string
    {
        return 'Get-CimInstance -ClassName Win32_ComputerSystem | Select-Object -Property NumberOfLogicalProcessors';
    }
    public function toString() : string
    {
        return 'CmiCmdletLogicalFinder';
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
