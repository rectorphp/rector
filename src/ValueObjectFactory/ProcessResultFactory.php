<?php

declare(strict_types=1);

namespace Rector\Core\ValueObjectFactory;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\ProcessResult;
use Rector\PostRector\Collector\NodesToRemoveCollector;

final class ProcessResultFactory
{
    public function __construct(
        private RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        private NodesToRemoveCollector $nodesToRemoveCollector
    ) {
    }

    /**
     * @param File[] $files
     */
    public function create(array $files): ProcessResult
    {
        $fileDiffs = [];
        $errors = [];

        foreach ($files as $file) {
            $errors = array_merge($errors, $file->getErrors());

            if ($file->getFileDiff() === null) {
                continue;
            }

            $fileDiffs[] = $file->getFileDiff();
        }

        return new ProcessResult(
            $fileDiffs,
            $errors,
            $this->removedAndAddedFilesCollector->getAddedFileCount(),
            $this->removedAndAddedFilesCollector->getRemovedFilesCount(),
            $this->nodesToRemoveCollector->getCount(),
        );
    }
}
