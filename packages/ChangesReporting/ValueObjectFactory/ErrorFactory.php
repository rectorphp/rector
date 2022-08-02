<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use PHPStan\AnalysedCodeException;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\ValueObject\Error\SystemError;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
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
