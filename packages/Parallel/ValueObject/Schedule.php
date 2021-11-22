<?php

declare(strict_types=1);

namespace Rector\Parallel\ValueObject;

/**
 * From
 * https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92#diff-bc84213b079ef3456caece03c00ba34c07886dcae12180cd1192fbb223d65b15
 */
final class Schedule
{
    /**
     * @param array<array<string>> $jobs
     */
    public function __construct(
        private int $numberOfProcesses,
        private array $jobs
    ) {
    }

    public function getNumberOfProcesses(): int
    {
        return $this->numberOfProcesses;
    }

    /**
     * @return array<array<string>>
     */
    public function getJobs(): array
    {
        return $this->jobs;
    }
}
