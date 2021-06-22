<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Processor;

use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;

interface FileProcessorInterface
{
    // @todo wait for implementers to adapt with 2nd parameters of Configuration $configuration
    // public function supports(File $file, Configuration $configuration): bool;


    // public function process(array $files, Configuration $configuration): void;

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array;
}
