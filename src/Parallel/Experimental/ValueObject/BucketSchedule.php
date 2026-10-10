<?php

declare (strict_types=1);
namespace Rector\Parallel\Experimental\ValueObject;

/**
 * @experimental Filled by @see \Rector\Parallel\Experimental\LptScheduleFactory, consumed only by the
 * "--lpt" parallel run.
 *
 * Unlike @see \Rector\Parallel\ValueObject\Schedule, jobs are not a shared pool: every worker owns its
 * own queue, decided up front.
 */
final class BucketSchedule
{
    /**
     * @var array<int, array<int, array<string>>>
     * @readonly
     */
    private array $jobsPerWorker;
    /**
     * @param array<int, array<int, array<string>>> $jobsPerWorker
     */
    public function __construct(array $jobsPerWorker)
    {
        $this->jobsPerWorker = $jobsPerWorker;
    }
    /**
     * @return array<int, array<int, array<string>>>
     */
    public function getJobsPerWorker(): array
    {
        return $this->jobsPerWorker;
    }
}
