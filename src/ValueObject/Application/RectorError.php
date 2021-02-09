<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Application;

use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorError
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var SmartFileInfo
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

    public function __construct(
        SmartFileInfo $smartFileInfo,
        string $message,
        ?int $line = null,
        ?string $rectorClass = null
    ) {
        $this->fileInfo = $smartFileInfo;
        $this->message = $message;
        $this->line = $line;
        $this->rectorClass = $rectorClass;
    }

    private function getRelativeFilePath(): string
    {
        return $this->fileInfo->getRelativeFilePathFromCwd();
    }

    private function getFileInfo(): SmartFileInfo
    {
        return $this->fileInfo;
    }

    private function getMessage(): string
    {
        return $this->message;
    }

    private function getLine(): ?int
    {
        return $this->line;
    }

    private function getRectorClass(): ?string
    {
        return $this->rectorClass;
    }
}
