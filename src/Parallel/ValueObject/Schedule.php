<?php

declare (strict_types=1);
namespace Rector\Parallel\ValueObject;

/**
 * Each worker owns a queue of jobs, balanced up front by @see \Rector\Parallel\ScheduleFactory.
 */
final class Schedule
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
