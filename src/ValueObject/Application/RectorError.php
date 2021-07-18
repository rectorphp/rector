<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Application;

use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorError
{
    public function __construct(
        private string $message,
        private SmartFileInfo $fileInfo,
        private ?int $line = null,
        private ?string $rectorClass = null
    ) {
    }

    public function getRelativeFilePath(): string
    {
        return $this->fileInfo->getRelativeFilePathFromCwd();
    }

    public function getFileInfo(): SmartFileInfo
    {
        return $this->fileInfo;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getRectorClass(): ?string
    {
        return $this->rectorClass;
    }
}
