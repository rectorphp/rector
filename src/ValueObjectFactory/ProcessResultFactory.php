<?php

declare(strict_types=1);

namespace Rector\Core\ValueObjectFactory;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\SystemError;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use Rector\PostRector\Collector\NodesToRemoveCollector;

final class ProcessResultFactory
{
    public function __construct(
        private readonly RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        private readonly NodesToRemoveCollector $nodesToRemoveCollector
    ) {
    }

    /**
     * @param array{system_errors: SystemError[], file_diffs: FileDiff[]} $errorsAndFileDiffs
     */
    public function create(array $errorsAndFileDiffs): ProcessResult
    {
        $systemErrors = $errorsAndFileDiffs[Bridge::SYSTEM_ERRORS];
        $fileDiffs = $errorsAndFileDiffs[Bridge::FILE_DIFFS];

        return new ProcessResult(
            $systemErrors,
            $fileDiffs,
            $this->removedAndAddedFilesCollector->getAddedFileCount(),
            $this->removedAndAddedFilesCollector->getRemovedFilesCount(),
            $this->nodesToRemoveCollector->getCount(),
        );
    }
}
