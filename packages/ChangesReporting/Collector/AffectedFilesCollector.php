<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Collector;

use Rector\Core\ValueObject\Application\File;
final class AffectedFilesCollector
{
    /**
     * @var File[]
     */
    private $affectedFiles = [];
    public function addFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $this->affectedFiles[$smartFileInfo->getRealPath()] = $file;
    }
    public function getNext() : ?\Rector\Core\ValueObject\Application\File
    {
        if ($this->affectedFiles !== []) {
            return \current($this->affectedFiles);
        }
        return null;
    }
    public function removeFromList(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        unset($this->affectedFiles[$smartFileInfo->getRealPath()]);
    }
}
