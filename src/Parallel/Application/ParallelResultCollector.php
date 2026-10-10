<?php

declare (strict_types=1);
namespace Rector\Parallel\Application;

use Rector\Parallel\ValueObject\Bridge;
use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\ProcessResult;
use Rector\ValueObject\Reporting\FileDiff;
/**
 * Gathers what the workers report back over the run, so a parallel file processor only has to decide
 * who gets which files.
 *
 * Holds mutable per-run state - create one per process() call, never share it.
 */
final class ParallelResultCollector
{
    /**
     * @var SystemError[]
     */
    private array $systemErrors = [];
    /**
     * @var FileDiff[]
     */
    private array $fileDiffs = [];
    /**
     * Paths are kept as keys while collecting, to deduplicate them per skip
     *
     * @var array<string, array<string, true>>
     */
    private array $usedSkips = [];
    private int $totalChanged = 0;
    /**
     * @param array{
     *      total_changed: int,
     *      system_errors: mixed[],
     *      file_diffs: array<string, mixed>,
     *      files_count: int,
     *      system_errors_count: int,
     *      used_skips: array<string, string[]>
     * } $json
     * @return int files covered by this result, to advance the progress bar
     */
    public function collectWorkerResult(array $json): int
    {
        $this->totalChanged += $json[Bridge::TOTAL_CHANGED];
        foreach ($json[Bridge::USED_SKIPS] as $skip => $paths) {
            $this->usedSkips[$skip] ??= [];
            foreach ($paths as $path) {
                $this->usedSkips[$skip][$path] = \true;
            }
        }
        // decode arrays to objects
        foreach ($json[Bridge::SYSTEM_ERRORS] as $jsonError) {
            if (is_string($jsonError)) {
                $this->systemErrors[] = new SystemError('System error: ' . $jsonError);
                continue;
            }
            $this->systemErrors[] = SystemError::decode($jsonError);
        }
        foreach ($json[Bridge::FILE_DIFFS] as $jsonFileDiff) {
            $this->fileDiffs[] = FileDiff::decode($jsonFileDiff);
        }
        return $json[Bridge::FILES_COUNT];
    }
    public function collectSystemError(SystemError $systemError): void
    {
        $this->systemErrors[] = $systemError;
    }
    public function createProcessResult(): ProcessResult
    {
        $mergedUsedSkips = [];
        foreach ($this->usedSkips as $skip => $paths) {
            $mergedUsedSkips[$skip] = array_keys($paths);
        }
        return new ProcessResult($this->systemErrors, $this->fileDiffs, $this->totalChanged, $mergedUsedSkips);
    }
}
