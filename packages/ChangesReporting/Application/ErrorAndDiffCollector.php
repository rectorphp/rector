<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Application;

use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\PostRector\Collector\NodesToRemoveCollector;

final class ErrorAndDiffCollector
{
    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    public function __construct(
        NodesToRemoveCollector $nodesToRemoveCollector,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector
    ) {
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
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
}
