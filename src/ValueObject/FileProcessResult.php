<?php

declare (strict_types=1);
namespace Rector\ValueObject;

use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\Reporting\FileDiff;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class FileProcessResult
{
    /**
     * @var SystemError[]
     * @readonly
     */
    private array $systemErrors;
    /**
     * @readonly
     */
    private ?FileDiff $fileDiff;
    /**
     * @param SystemError[] $systemErrors
     */
    public function __construct(array $systemErrors, ?FileDiff $fileDiff)
    {
        $this->systemErrors = $systemErrors;
        $this->fileDiff = $fileDiff;
        Assert::allIsInstanceOf($systemErrors, SystemError::class);
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
}
