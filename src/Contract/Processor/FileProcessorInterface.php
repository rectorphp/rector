<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Processor;

use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
interface FileProcessorInterface
{
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool;
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : array;
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array;
}
