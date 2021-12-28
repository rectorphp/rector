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
    public function __construct(\Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, \Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector)
    {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }
    /**
     * @param array{system_errors: SystemError[], file_diffs: FileDiff[]} $errorsAndFileDiffs
     */
    public function create(array $errorsAndFileDiffs) : \Rector\Core\ValueObject\ProcessResult
    {
        $systemErrors = $errorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS];
        $fileDiffs = $errorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS];
        return new \Rector\Core\ValueObject\ProcessResult($systemErrors, $fileDiffs, $this->removedAndAddedFilesCollector->getAddedFileCount(), $this->removedAndAddedFilesCollector->getRemovedFilesCount(), $this->nodesToRemoveCollector->getCount());
    }
}
