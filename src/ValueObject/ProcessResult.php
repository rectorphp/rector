<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject;

use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix202305\Webmozart\Assert\Assert;
/**
 * @see \Rector\Core\ValueObjectFactory\ProcessResultFactory
 */
final class ProcessResult
{
    /**
     * @var SystemError[]
     * @readonly
     */
    private $systemErrors;
    /**
     * @var FileDiff[]
     * @readonly
     */
    private $fileDiffs;
    /**
     * @readonly
     * @var int
     */
    private $addedFilesCount;
    /**
     * @readonly
     * @var int
     */
    private $removedFilesCount;
    /**
     * @readonly
     * @var int
     */
    private $removedNodeCount;
    /**
     * @param FileDiff[] $fileDiffs
     * @param SystemError[] $systemErrors
     */
    public function __construct(array $systemErrors, array $fileDiffs, int $addedFilesCount, int $removedFilesCount, int $removedNodeCount)
    {
        $this->systemErrors = $systemErrors;
        $this->fileDiffs = $fileDiffs;
        $this->addedFilesCount = $addedFilesCount;
        $this->removedFilesCount = $removedFilesCount;
        $this->removedNodeCount = $removedNodeCount;
        Assert::allIsAOf($fileDiffs, FileDiff::class);
        Assert::allIsAOf($systemErrors, SystemError::class);
    }
    /**
     * @return FileDiff[]
     */
    public function getFileDiffs() : array
    {
        return $this->fileDiffs;
    }
    /**
     * @return SystemError[]
     */
    public function getErrors() : array
    {
        return $this->systemErrors;
    }
    public function getAddedFilesCount() : int
    {
        return $this->addedFilesCount;
    }
    public function getRemovedFilesCount() : int
    {
        return $this->removedFilesCount;
    }
    public function getRemovedAndAddedFilesCount() : int
    {
        return $this->removedFilesCount + $this->addedFilesCount;
    }
    public function getRemovedNodeCount() : int
    {
        return $this->removedNodeCount;
    }
}
