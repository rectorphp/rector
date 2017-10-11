<?php declare(strict_types=1);

namespace Rector\FileSystem;

use SplFileInfo;

/**
 * Consider using NodeTraverser, same as for namespace to add to node.
 * Would allow united API.
 */
final class CurrentFileProvider
{
    /**
     * @var SplFileInfo|null
     */
    private $fileInfo;

    public function setCurrentFile(SplFileInfo $fileInfo): void
    {
        $this->fileInfo = $fileInfo;
    }

    public function getCurrentFile(): ?SplFileInfo
    {
        return $this->fileInfo;
    }
}
