<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use PHPStan\Collectors\CollectedData;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix202312\Webmozart\Assert\Assert;
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
    private $collectedDatas;
    /**
     * @param SystemError[] $systemErrors
     * @param CollectedData[] $collectedDatas
     */
    public function __construct(array $systemErrors, ?FileDiff $fileDiff, array $collectedDatas)
    {
        $this->systemErrors = $systemErrors;
        $this->fileDiff = $fileDiff;
        $this->collectedDatas = $collectedDatas;
        Assert::allIsInstanceOf($systemErrors, SystemError::class);
        Assert::allIsInstanceOf($collectedDatas, CollectedData::class);
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
        return $this->collectedDatas;
    }
}
