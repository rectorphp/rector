<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use PHPStan\Collectors\CollectedData;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix202312\Webmozart\Assert\Assert;
final class ProcessResult
{
    /**
     * @var SystemError[]
     */
    private $systemErrors;
    /**
     * @var FileDiff[]
     * @readonly
     */
    private $fileDiffs;
    /**
     * @var CollectedData[]
     * @readonly
     */
    private $collectedData;
    /**
     * @param SystemError[] $systemErrors
     * @param FileDiff[] $fileDiffs
     * @param CollectedData[] $collectedData
     */
    public function __construct(array $systemErrors, array $fileDiffs, array $collectedData)
    {
        $this->systemErrors = $systemErrors;
        $this->fileDiffs = $fileDiffs;
        $this->collectedData = $collectedData;
        Assert::allIsInstanceOf($systemErrors, SystemError::class);
        Assert::allIsInstanceOf($fileDiffs, FileDiff::class);
        Assert::allIsInstanceOf($collectedData, CollectedData::class);
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
    public function getFileDiffs() : array
    {
        return $this->fileDiffs;
    }
    /**
     * @return CollectedData[]
     */
    public function getCollectedData() : array
    {
        return $this->collectedData;
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
