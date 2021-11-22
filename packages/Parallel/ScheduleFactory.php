<?php

declare(strict_types=1);

namespace Rector\Parallel;

use Rector\Parallel\ValueObject\Schedule;

/**
 * Used from
 * https://github.com/phpstan/phpstan-src/blob/9124c66dcc55a222e21b1717ba5f60771f7dda92/src/Parallel/Scheduler.php
 *
 * @api @todo complete parallel run
 */
final class ScheduleFactory
{
    /**
     * @param array<string> $files
     */
    public function create(int $cpuCores, int $jobSize, array $files): Schedule
    {
        $jobs = array_chunk($files, $jobSize);
        $numberOfProcesses = min(count($jobs), $cpuCores);

        return new Schedule($numberOfProcesses, $jobs);
    }
}
