<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Application;

use PhpParser\Node;
use PHPStan\AnalysedCodeException;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Differ\DefaultDiffer;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\ValueObject\Application\RectorError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;
use Symplify\SmartFileSystem\SmartFileInfo;
use Throwable;

final class ErrorAndDiffCollector
{
    /**
     * @var RectorError[]
     */
    private $errors = [];

    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

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
     * @var ConsoleDiffer
     */
    private $consoleDiffer;

    /**
     * @var DefaultDiffer
     */
    private $defaultDiffer;

    public function __construct(
        ExceptionCorrector $exceptionCorrector,
        NodesToRemoveCollector $nodesToRemoveCollector,
        RectorChangeCollector $rectorChangeCollector,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        ConsoleDiffer $consoleDiffer,
        DefaultDiffer $defaultDiffer
    ) {
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->exceptionCorrector = $exceptionCorrector;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->consoleDiffer = $consoleDiffer;
        $this->defaultDiffer = $defaultDiffer;
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

    public function getAddFilesCount(): int
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

    public function addFileDiff(SmartFileInfo $smartFileInfo, string $newContent, string $oldContent): void
    {
        if ($newContent === $oldContent) {
            return;
        }

        $rectorChanges = $this->rectorChangeCollector->getRectorChangesByFileInfo($smartFileInfo);

        // always keep the most recent diff
        $fileDiff = new FileDiff(
            $smartFileInfo,
            $this->defaultDiffer->diff($oldContent, $newContent),
            $this->consoleDiffer->diff($oldContent, $newContent),
            $rectorChanges
        );
        $this->fileDiffs[$smartFileInfo->getRealPath()] = $fileDiff;
    }

    /**
     * @return FileDiff[]
     */
    public function getFileDiffs(): array
    {
        return $this->fileDiffs;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getAffectedFileInfos(): array
    {
        $fileInfos = [];
        foreach ($this->fileDiffs as $fileDiff) {
            $fileInfos[] = $fileDiff->getFileInfo();
        }

        return array_unique($fileInfos);
    }

    public function getFileDiffsCount(): int
    {
        return count($this->fileDiffs);
    }

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
}
