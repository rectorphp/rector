<?php

declare (strict_types=1);
namespace Rector\ValueObject;

use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\Reporting\FileDiff;
use RectorPrefix202506\Webmozart\Assert\Assert;
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
     * @param SystemError[] $systemErrors
     * @param FileDiff[] $fileDiffs
     */
    public function __construct(array $systemErrors, array $fileDiffs)
    {
        $this->systemErrors = $systemErrors;
        $this->fileDiffs = $fileDiffs;
        Assert::allIsInstanceOf($systemErrors, SystemError::class);
        Assert::allIsInstanceOf($fileDiffs, FileDiff::class);
    }
    /**
     * @return SystemError[]
     */
    public function getSystemErrors() : array
    {
        return $this->systemErrors;
    }
    /**
     * @return FileDiff[]
     */
    public function getFileDiffs(bool $onlyWithChanges = \true) : array
    {
        if ($onlyWithChanges) {
            return \array_filter($this->fileDiffs, fn(FileDiff $fileDiff): bool => $fileDiff->getDiff() !== '');
        }
        return $this->fileDiffs;
    }
    /**
     * @param SystemError[] $systemErrors
     */
    public function addSystemErrors(array $systemErrors) : void
    {
        Assert::allIsInstanceOf($systemErrors, SystemError::class);
        $this->systemErrors = \array_merge($this->systemErrors, $systemErrors);
    }
}
