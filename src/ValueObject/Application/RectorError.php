<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Application;

use Symplify\SmartFileSystem\SmartFileInfo;
final class RectorError
{
    /**
     * @var string
     */
    private $message;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $fileInfo;
    /**
     * @var int|null
     */
    private $line;
    /**
     * @var string|null
     */
    private $rectorClass;
    public function __construct(string $message, \Symplify\SmartFileSystem\SmartFileInfo $fileInfo, ?int $line = null, ?string $rectorClass = null)
    {
        $this->message = $message;
        $this->fileInfo = $fileInfo;
        $this->line = $line;
        $this->rectorClass = $rectorClass;
    }
    public function getRelativeFilePath() : string
    {
        return $this->fileInfo->getRelativeFilePathFromCwd();
    }
    public function getFileInfo() : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->fileInfo;
    }
    public function getMessage() : string
    {
        return $this->message;
    }
    public function getLine() : ?int
    {
        return $this->line;
    }
    public function getRectorClass() : ?string
    {
        return $this->rectorClass;
    }
}
