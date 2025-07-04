<?php

declare (strict_types=1);
namespace RectorPrefix202507\Symplify\EasyParallel\ValueObject;

/**
 * From
 * https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92#diff-bc84213b079ef3456caece03c00ba34c07886dcae12180cd1192fbb223d65b15
 *
 * @api
 */
final class Schedule
{
    /**
     * @readonly
     */
    private int $numberOfProcesses;
    /**
     * @var array<array<string>>
     * @readonly
     */
    private array $jobs;
    /**
     * @param array<array<string>> $jobs
     */
    public function __construct(int $numberOfProcesses, array $jobs)
    {
        $this->numberOfProcesses = $numberOfProcesses;
        $this->jobs = $jobs;
    }
    public function getNumberOfProcesses() : int
    {
        return $this->numberOfProcesses;
    }
    /**
     * @return array<array<string>>
     */
    public function getJobs() : array
    {
        return $this->jobs;
    }
}
