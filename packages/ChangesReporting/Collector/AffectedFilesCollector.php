<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Collector;

use Rector\Core\ValueObject\Application\File;
final class AffectedFilesCollector
{
    /**
     * @var array<string, File>
     */
    private $affectedFiles = [];
    public function addFile(File $file) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $this->affectedFiles[$smartFileInfo->getRealPath()] = $file;
    }
    public function getNext() : ?File
    {
        if ($this->affectedFiles !== []) {
            return \current($this->affectedFiles);
        }
        return null;
    }
    public function removeFromList(File $file) : void
    {
        $smartFileInfo = $file->getSmartFileInfo();
        unset($this->affectedFiles[$smartFileInfo->getRealPath()]);
    }
}
