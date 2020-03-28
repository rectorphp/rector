<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Application;

use PhpParser\Node;
use PHPStan\AnalysedCodeException;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Error\ExceptionCorrector;
use Rector\Core\PhpParser\Node\Commander\NodeRemovingCommander;
use Rector\Core\ValueObject\Application\Error;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Symplify\SmartFileSystem\SmartFileInfo;
use Throwable;

final class ErrorAndDiffCollector
{
    /**
     * @var Error[]
     */
    private $errors = [];

    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];

    /**
     * @var DifferAndFormatter
     */
    private $differAndFormatter;

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
     * @var NodeRemovingCommander
     */
    private $nodeRemovingCommander;

    public function __construct(
        DifferAndFormatter $differAndFormatter,
        RectorChangeCollector $rectorChangeCollector,
        ExceptionCorrector $exceptionCorrector,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        NodeRemovingCommander $nodeRemovingCommander
    ) {
        $this->differAndFormatter = $differAndFormatter;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->exceptionCorrector = $exceptionCorrector;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->nodeRemovingCommander = $nodeRemovingCommander;
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRemovedAndAddedFilesCount(): int
    {
        return $this->removedAndAddedFilesCollector->getAffectedFilesCount();
    }

    public function getRemovedNodeCount(): int
    {
        return $this->nodeRemovingCommander->getCount();
    }

    /**
     * @return Node[]
     */
    public function getRemovedNodes(): array
    {
        return $this->nodeRemovingCommander->getNodesToRemove();
    }

    public function addFileDiff(SmartFileInfo $smartFileInfo, string $newContent, string $oldContent): void
    {
        if ($newContent === $oldContent) {
            return;
        }

        $rectorChanges = $this->rectorChangeCollector->getRectorChangesByFileInfo($smartFileInfo);

        // always keep the most recent diff
        $this->fileDiffs[$smartFileInfo->getRealPath()] = new FileDiff(
            $smartFileInfo,
            $this->differAndFormatter->diff($oldContent, $newContent),
            $this->differAndFormatter->diffAndFormat($oldContent, $newContent),
            $rectorChanges
        );
    }

    /**
     * @return FileDiff[]
     */
    public function getFileDiffs(): array
    {
        return $this->fileDiffs;
    }

    public function getFileDiffsCount(): int
    {
        return count($this->getFileDiffs());
    }

    public function addAutoloadError(AnalysedCodeException $analysedCodeException, SmartFileInfo $fileInfo): void
    {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);

        $this->errors[] = new Error($fileInfo, $message);
    }

    public function addErrorWithRectorClassMessageAndFileInfo(
        string $rectorClass,
        string $message,
        SmartFileInfo $smartFileInfo
    ): void {
        $this->errors[] = new Error($smartFileInfo, $message, null, $rectorClass);
    }

    public function addThrowableWithFileInfo(Throwable $throwable, SmartFileInfo $fileInfo): void
    {
        $rectorClass = $this->exceptionCorrector->matchRectorClass($throwable);
        if ($rectorClass) {
            $this->addErrorWithRectorClassMessageAndFileInfo($rectorClass, $throwable->getMessage(), $fileInfo);
        } else {
            $this->errors[] = new Error($fileInfo, $throwable->getMessage(), $throwable->getCode());
        }
    }
}
