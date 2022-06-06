<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\ValueObject\Application;

use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\Rector\FileSystemRector\Contract\FileWithNodesInterface;
final class MovedFile implements FileWithNodesInterface
{
    /**
     * @readonly
     * @var \Rector\Core\ValueObject\Application\File
     */
    private $file;
    /**
     * @readonly
     * @var string
     */
    private $newFilePath;
    public function __construct(File $file, string $newFilePath)
    {
        $this->file = $file;
        $this->newFilePath = $newFilePath;
    }
    public function getFile() : File
    {
        return $this->file;
    }
    public function getNewFilePath() : string
    {
        return $this->newFilePath;
    }
    /**
     * @return Stmt[]
     */
    public function getNodes() : array
    {
        return $this->file->getNewStmts();
    }
    public function getFilePath() : string
    {
        $smartFileInfo = $this->file->getSmartFileInfo();
        return $smartFileInfo->getRelativeFilePath();
    }
}
