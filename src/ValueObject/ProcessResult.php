<?php

declare (strict_types=1);
namespace Rector\ValueObject;

use Rector\Contract\Rector\RectorInterface;
use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\Reporting\FileDiff;
use RectorPrefix202604\Webmozart\Assert\Assert;
final class ProcessResult
{
    /**
     * @var SystemError[]
     */
    private array $systemErrors;
    /**
     * @var FileDiff[]
     * @readonly
     */
    private array $fileDiffs;
    /**
     * @readonly
     */
    private int $totalChanged;
    /**
     * @param SystemError[] $systemErrors
     * @param FileDiff[] $fileDiffs
     */
    public function __construct(array $systemErrors, array $fileDiffs, int $totalChanged)
    {
        $this->systemErrors = $systemErrors;
        $this->fileDiffs = $fileDiffs;
        $this->totalChanged = $totalChanged;
        Assert::allIsInstanceOf($systemErrors, SystemError::class);
        Assert::allIsInstanceOf($fileDiffs, FileDiff::class);
    }
    /**
     * @return SystemError[]
     */
    public function getSystemErrors(): array
    {
        return $this->systemErrors;
    }
    /**
     * @return FileDiff[]
     */
    public function getFileDiffs(bool $onlyWithChanges = \true): array
    {
        if ($onlyWithChanges) {
            return array_filter($this->fileDiffs, fn(FileDiff $fileDiff): bool => $fileDiff->getDiff() !== '');
        }
        return $this->fileDiffs;
    }
    /**
     * @param SystemError[] $systemErrors
     */
    public function addSystemErrors(array $systemErrors): void
    {
        Assert::allIsInstanceOf($systemErrors, SystemError::class);
        $this->systemErrors = array_merge($this->systemErrors, $systemErrors);
    }
    public function getTotalChanged(): int
    {
        return $this->totalChanged;
    }
    /**
     * @return array<class-string<RectorInterface>, int>
     */
    public function getRuleApplicationCounts(): array
    {
        $ruleCounts = [];
        foreach ($this->fileDiffs as $fileDiff) {
            foreach ($fileDiff->getRectorClasses() as $rectorClass) {
                if (!isset($ruleCounts[$rectorClass])) {
                    $ruleCounts[$rectorClass] = 0;
                }
                ++$ruleCounts[$rectorClass];
            }
        }
        arsort($ruleCounts);
        return $ruleCounts;
    }
}
