<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\ChangesReporting\ValueObjectFactory;

use RectorPrefix20220606\PHPStan\AnalysedCodeException;
use RectorPrefix20220606\Rector\Core\Error\ExceptionCorrector;
use RectorPrefix20220606\Rector\Core\ValueObject\Error\SystemError;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ErrorFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Error\ExceptionCorrector
     */
    private $exceptionCorrector;
    public function __construct(ExceptionCorrector $exceptionCorrector)
    {
        $this->exceptionCorrector = $exceptionCorrector;
    }
    public function createAutoloadError(AnalysedCodeException $analysedCodeException, SmartFileInfo $smartFileInfo) : SystemError
    {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);
        return new SystemError($message, $smartFileInfo->getRelativeFilePathFromCwd());
    }
}
