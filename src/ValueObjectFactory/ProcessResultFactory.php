<?php

declare (strict_types=1);
namespace Rector\Core\ValueObjectFactory;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use Rector\PostRector\Collector\NodesToRemoveCollector;
final class ProcessResultFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    public function __construct(RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, NodesToRemoveCollector $nodesToRemoveCollector)
    {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }
    /**
     * @param array{system_errors: SystemError[], file_diffs: FileDiff[]} $errorsAndFileDiffs
     */
    public function create(array $errorsAndFileDiffs) : ProcessResult
    {
        $systemErrors = $errorsAndFileDiffs[Bridge::SYSTEM_ERRORS];
        $fileDiffs = $errorsAndFileDiffs[Bridge::FILE_DIFFS];
        return new ProcessResult($systemErrors, $fileDiffs, $this->removedAndAddedFilesCollector->getAddedFileCount(), $this->removedAndAddedFilesCollector->getRemovedFilesCount(), $this->nodesToRemoveCollector->getCount());
    }
}
