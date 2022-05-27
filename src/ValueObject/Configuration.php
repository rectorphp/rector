<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;

final class Configuration
{
    /**
     * @param string[] $fileExtensions
     * @param string[] $paths
     */
    public function __construct(
        private readonly bool $isDryRun = false,
        private readonly bool $showProgressBar = true,
        private readonly bool $shouldClearCache = false,
        private readonly string $outputFormat = ConsoleOutputFormatter::NAME,
        private readonly array $fileExtensions = ['php'],
        private readonly array $paths = [],
        private readonly bool $showDiffs = true,
        private readonly string | null $parallelPort = null,
        private readonly string | null $parallelIdentifier = null,
        private readonly bool $isParallel = false,
        private readonly string|null $memoryLimit = null
    ) {
    }

    public function isDryRun(): bool
    {
        return $this->isDryRun;
    }

    public function shouldShowProgressBar(): bool
    {
        return $this->showProgressBar;
    }

    public function shouldClearCache(): bool
    {
        return $this->shouldClearCache;
    }

    /**
     * @return string[]
     */
    public function getFileExtensions(): array
    {
        return $this->fileExtensions;
    }

    /**
     * @return string[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getOutputFormat(): string
    {
        return $this->outputFormat;
    }

    public function shouldShowDiffs(): bool
    {
        return $this->showDiffs;
    }

    public function getParallelPort(): ?string
    {
        return $this->parallelPort;
    }

    public function getParallelIdentifier(): ?string
    {
        return $this->parallelIdentifier;
    }

    public function isParallel(): bool
    {
        return $this->isParallel;
    }

    public function getMemoryLimit(): ?string
    {
        return $this->memoryLimit;
    }
}
