<?php

declare (strict_types=1);
namespace RectorPrefix202304\Symplify\EasyParallel;

use RectorPrefix202304\Symplify\EasyParallel\ValueObject\Schedule;
/**
 * Used from
 * https://github.com/phpstan/phpstan-src/blob/9124c66dcc55a222e21b1717ba5f60771f7dda92/src/Parallel/Scheduler.php
 *
 * @api
 */
final class ScheduleFactory
{
    /**
     * @param array<string> $files
     */
    public function create(int $cpuCores, int $jobSize, int $maxNumberOfProcesses, array $files) : Schedule
    {
        $jobs = \array_chunk($files, $jobSize);
        $numberOfProcesses = \min(\count($jobs), $cpuCores);
        $numberOfProcesses = \min($maxNumberOfProcesses, $numberOfProcesses);
        return new Schedule($numberOfProcesses, $jobs);
    }
}
