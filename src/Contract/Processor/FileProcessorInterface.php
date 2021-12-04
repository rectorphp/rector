<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Processor;

use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;

interface FileProcessorInterface
{
    public function supports(File $file, Configuration $configuration): bool;

    /**
     * @return mixed[]|void
     */
    public function process(File $file, Configuration $configuration);

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array;
}
