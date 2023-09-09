<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use PHPStan\Collectors\CollectedData;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
final class FileProcessResult
{
    /**
     * @var SystemError[]
     * @readonly
     */
    private $systemErrors;
    /**
     * @readonly
     * @var \Rector\Core\ValueObject\Reporting\FileDiff|null
     */
    private $fileDiff;
    /**
     * @var CollectedData[]
     * @readonly
     */
    private $collectedData;
    /**
     * @param SystemError[] $systemErrors
     * @param CollectedData[] $collectedData
     */
    public function __construct(array $systemErrors, ?FileDiff $fileDiff, array $collectedData)
    {
        $this->systemErrors = $systemErrors;
        $this->fileDiff = $fileDiff;
        $this->collectedData = $collectedData;
    }
    /**
     * @return SystemError[]
     */
    public function getSystemErrors() : array
    {
        return $this->systemErrors;
    }
    public function getFileDiff() : ?FileDiff
    {
        return $this->fileDiff;
    }
    /**
     * @return CollectedData[]
     */
    public function getCollectedData() : array
    {
        return $this->collectedData;
    }
}
