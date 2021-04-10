<?php
declare(strict_types=1);

namespace Rector\Core\Contract\Processor;

use Rector\Core\ValueObject\Application\File;

interface FileProcessorInterface
{
    public function supports(File $file): bool;

    public function process(File $file): void;

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array;
}
