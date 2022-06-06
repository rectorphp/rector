<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\Processor;

use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\Core\ValueObject\Configuration;
use RectorPrefix20220606\Rector\Core\ValueObject\Error\SystemError;
use RectorPrefix20220606\Rector\Core\ValueObject\Reporting\FileDiff;
interface FileProcessorInterface
{
    public function supports(File $file, Configuration $configuration) : bool;
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array;
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array;
}
