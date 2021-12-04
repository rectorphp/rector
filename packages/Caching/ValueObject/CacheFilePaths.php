<?php

declare(strict_types=1);

namespace Rector\Caching\ValueObject;

final class CacheFilePaths
{
    public function __construct(
        private readonly string $firstDirectory,
        private readonly string $secondDirectory,
        private readonly string $filePath
    ) {
    }

    public function getFirstDirectory(): string
    {
        return $this->firstDirectory;
    }

    public function getSecondDirectory(): string
    {
        return $this->secondDirectory;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
