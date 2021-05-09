<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Processor;

use Rector\Core\ValueObject\Application\File;
interface FileProcessorInterface
{
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool;
    /**
     * @param File[] $files
     */
    public function process(array $files) : void;
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array;
}
