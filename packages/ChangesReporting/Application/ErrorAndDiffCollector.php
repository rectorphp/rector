<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Application;

use PHPStan\AnalysedCodeException;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\RectorError;
use Rector\PostRector\Collector\NodesToRemoveCollector;

final class ErrorAndDiffCollector
{
    /**
     * @var RectorError[]
     */
    private $errors = [];

    /**
     * @var ExceptionCorrector
     */
    private $exceptionCorrector;

    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    public function __construct(
        ExceptionCorrector $exceptionCorrector,
        NodesToRemoveCollector $nodesToRemoveCollector,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
    ) {
        $this->exceptionCorrector = $exceptionCorrector;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }

    /**
     * @return RectorError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRemovedAndAddedFilesCount(): int
    {
        return $this->removedAndAddedFilesCollector->getAffectedFilesCount();
    }

    public function getAddedFilesCount(): int
    {
        return $this->removedAndAddedFilesCollector->getAddedFileCount();
    }

    public function getRemovedFilesCount(): int
    {
        return $this->removedAndAddedFilesCollector->getRemovedFilesCount();
    }

    public function getRemovedNodeCount(): int
    {
        return $this->nodesToRemoveCollector->getCount();
    }

    public function addAutoloadError(AnalysedCodeException $analysedCodeException, File $file): void
    {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);
        $rectorError = new RectorError($message);

        $file->addRectorError($rectorError);
    }
}
