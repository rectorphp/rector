<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use PHPStan\AnalysedCodeException;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\ValueObject\Application\RectorError;
final class ErrorFactory
{
    /**
     * @var ExceptionCorrector
     */
    private $exceptionCorrector;
    public function __construct(\Rector\Core\Error\ExceptionCorrector $exceptionCorrector)
    {
        $this->exceptionCorrector = $exceptionCorrector;
    }
    public function createAutoloadError(\PHPStan\AnalysedCodeException $analysedCodeException) : \Rector\Core\ValueObject\Application\RectorError
    {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);
        return new \Rector\Core\ValueObject\Application\RectorError($message);
    }
}
