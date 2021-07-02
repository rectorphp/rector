<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Processor;

use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;

interface FileProcessorInterface
{
    public function supports(File $file, Configuration $configuration): bool;

    public function process(File $file, Configuration $configuration): void;

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array;
}
