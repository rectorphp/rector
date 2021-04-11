<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Application;

use PhpParser\Node;
use PHPStan\AnalysedCodeException;
use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\RectorError;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Symplify\SmartFileSystem\SmartFileInfo;
use Throwable;

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

    /**
     * @var FileDiffFactory
     */
    private $fileDiffFactory;

    public function __construct(
        ExceptionCorrector $exceptionCorrector,
        NodesToRemoveCollector $nodesToRemoveCollector,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        FileDiffFactory $fileDiffFactory
    ) {
        $this->exceptionCorrector = $exceptionCorrector;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->fileDiffFactory = $fileDiffFactory;
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

    /**
     * @return Node[]
     */
    public function getRemovedNodes(): array
    {
        return $this->nodesToRemoveCollector->getNodesToRemove();
    }

    public function addFileDiff(File $file, string $oldContent, string $newContent): void
    {
        if ($newContent === $oldContent) {
            return;
        }

        $fileDiff = $this->fileDiffFactory->createFileDiff($file, $oldContent, $newContent);
        $file->setFileDiff($fileDiff);
    }

//    /**
//     * @return FileDiff[]
//     */
//    public function getFileDiffs(): array
//    {
//        return $this->fileDiffs;
//    }

//    /**
//     * @return SmartFileInfo[]
//     */
//    public function getAffectedFileInfos(): array
//    {
//        $fileInfos = [];
//        foreach ($this->fileDiffs as $fileDiff) {
//            $fileInfos[] = $fileDiff->getFileInfo();
//        }
//
//        return array_unique($fileInfos);
//    }

//    public function getFileDiffsCount(): int
//    {
//        return count($this->fileDiffs);
//    }

    public function addAutoloadError(AnalysedCodeException $analysedCodeException, SmartFileInfo $fileInfo): void
    {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);
        $this->errors[] = new RectorError($fileInfo, $message);
    }

    public function addErrorWithRectorClassMessageAndFileInfo(
        string $rectorClass,
        string $message,
        SmartFileInfo $smartFileInfo
    ): void {
        $this->errors[] = new RectorError($smartFileInfo, $message, null, $rectorClass);
    }

    public function addThrowableWithFileInfo(Throwable $throwable, SmartFileInfo $fileInfo): void
    {
        $rectorClass = $this->exceptionCorrector->matchRectorClass($throwable);
        if ($rectorClass) {
            $this->addErrorWithRectorClassMessageAndFileInfo($rectorClass, $throwable->getMessage(), $fileInfo);
        } else {
            $this->errors[] = new RectorError($fileInfo, $throwable->getMessage(), $throwable->getCode());
        }
    }

    public function hasErrors(SmartFileInfo $phpFileInfo): bool
    {
        foreach ($this->errors as $error) {
            if ($error->getFileInfo() === $phpFileInfo) {
                return true;
            }
        }

        return false;
    }
}
