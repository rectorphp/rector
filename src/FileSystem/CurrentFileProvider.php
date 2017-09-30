<?php declare(strict_types=1);

namespace Rector\FileSystem;

use SplFileInfo;

final class CurrentFileProvider
{
    /**
     * @var SplFileInfo
     */
    private $fileInfo;

    public function setCurrentFile(SplFileInfo $fileInfo): void
    {
        $this->fileInfo = $fileInfo;
    }

    public function getCurrentFile(): SplFileInfo
    {
        return $this->fileInfo;
    }
}
