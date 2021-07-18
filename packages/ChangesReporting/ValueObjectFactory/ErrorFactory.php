<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObjectFactory;

use PHPStan\AnalysedCodeException;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\ValueObject\Application\RectorError;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ErrorFactory
{
    public function __construct(
        private ExceptionCorrector $exceptionCorrector
    ) {
    }

    public function createAutoloadError(AnalysedCodeException $analysedCodeException, SmartFileInfo $smartFileInfo): RectorError
    {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);
        return new RectorError($message, $smartFileInfo);
    }
}
