<?php declare(strict_types=1);

namespace Rector\Application;

use PHPStan\AnalysedCodeException;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\Error\ExceptionCorrector;
use Rector\Reporting\FileDiff;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
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
     * @var AppliedRectorCollector
     */
    private $appliedRectorCollector;

    /**
     * @var ExceptionCorrector
     */
    private $exceptionCorrector;

    public function __construct(
        DifferAndFormatter $differAndFormatter,
        AppliedRectorCollector $appliedRectorCollector,
        ExceptionCorrector $exceptionCorrector
    ) {
        $this->differAndFormatter = $differAndFormatter;
        $this->appliedRectorCollector = $appliedRectorCollector;
        $this->exceptionCorrector = $exceptionCorrector;
    }

    public function addError(Error $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addFileDiff(SmartFileInfo $smartFileInfo, string $newContent, string $oldContent): void
    {
        if ($newContent === $oldContent) {
            return;
        }

        $appliedRectors = $this->appliedRectorCollector->getRectorClasses($smartFileInfo);

        // always keep the most recent diff
        $this->fileDiffs[$smartFileInfo->getRealPath()] = new FileDiff(
            $smartFileInfo->getRealPath(),
            $this->differAndFormatter->diffAndFormat($oldContent, $newContent),
            $appliedRectors
        );
    }

    /**
     * @return FileDiff[]
     */
    public function getFileDiffs(): array
    {
        return $this->fileDiffs;
    }

    public function addAutoloadError(AnalysedCodeException $analysedCodeException, SmartFileInfo $fileInfo): void
    {
        $message = $this->exceptionCorrector->getAutoloadExceptionMessageAndAddLocation($analysedCodeException);

        $this->addError(new Error($fileInfo, $message));
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
            $this->addError(new Error($fileInfo, $throwable->getMessage(), $throwable->getCode()));
        }
    }
}
