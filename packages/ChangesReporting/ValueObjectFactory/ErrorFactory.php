<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use PHPStan\AnalysedCodeException;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\ValueObject\Application\RectorError;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ErrorFactory
{
    /**
     * @var \Rector\Core\Error\ExceptionCorrector
     */
    private $exceptionCorrector;
    public function __construct(\Rector\Core\Error\ExceptionCorrector $exceptionCorrector)
    {
        $this->exceptionCorrector = $exceptionCorrector;
    }
    public function createAutoloadError(\PHPStan\AnalysedCodeException $analysedCodeException, \Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : \Rector\Core\ValueObject\Application\RectorError
    {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);
        return new \Rector\Core\ValueObject\Application\RectorError($message, $smartFileInfo);
    }
}
