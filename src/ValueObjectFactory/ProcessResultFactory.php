<?php

declare(strict_types=1);

namespace Rector\Core\ValueObjectFactory;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\ProcessResult;

final class ProcessResultFactory
{
    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    public function __construct(ErrorAndDiffCollector $errorAndDiffCollector)
    {
        $this->errorAndDiffCollector = $errorAndDiffCollector;
    }

    /**
     * @param File[] $files
     */
    public function create(array $files): ProcessResult
    {
        $fileDiffs = [];
        $errors = [];

        foreach ($files as $file) {
            if ($file->getFileDiff() === null) {
                continue;
            }

            $errors = array_merge($errors, $file->getErrors());
            $fileDiffs[] = $file->getFileDiff();
        }

        return new ProcessResult(
            $fileDiffs,
            $errors,
            $this->errorAndDiffCollector->getAddedFilesCount(),
            $this->errorAndDiffCollector->getRemovedFilesCount(),
            $this->errorAndDiffCollector->getRemovedNodeCount(),
        );
    }
}
