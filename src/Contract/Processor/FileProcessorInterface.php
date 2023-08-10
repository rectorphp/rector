<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Processor;

use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
/**
 * @internal
 */
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
