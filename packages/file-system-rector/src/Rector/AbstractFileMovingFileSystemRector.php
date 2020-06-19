<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Rector;

use Rector\Autodiscovery\FileMover\FileMover;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestination;

abstract class AbstractFileMovingFileSystemRector extends AbstractFileSystemRector
{
    /**
     * @var FileMover
     */
    protected $fileMover;

    /**
     * @required
     */
    public function autowireAbstractFileMovingFileSystemRector(FileMover $fileMover): void
    {
        $this->fileMover = $fileMover;
    }

    protected function processNodesWithFileDestination(?NodesWithFileDestination $nodesWithFileDestination): void
    {
        // nothing to move
        if ($nodesWithFileDestination === null) {
            return;
        }

        $this->removeFile($nodesWithFileDestination->getOriginalSmartFileInfo());

        $this->addClassRename(
            $nodesWithFileDestination->getOldClassName(),
            $nodesWithFileDestination->getNewClassName()
        );

        $this->printNodesWithFileDestination($nodesWithFileDestination);
    }
}
