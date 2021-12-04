<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObjectFactory;

use PHPStan\AnalysedCodeException;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\ValueObject\Application\SystemError;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ErrorFactory
{
    public function __construct(
        private readonly ExceptionCorrector $exceptionCorrector
    ) {
    }

    public function createAutoloadError(
        AnalysedCodeException $analysedCodeException,
        SmartFileInfo $smartFileInfo
    ): SystemError {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);
        return new SystemError($message, $smartFileInfo->getRelativeFilePathFromCwd());
    }
}
