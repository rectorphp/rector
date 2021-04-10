<?php
declare(strict_types=1);

namespace Rector\Core\Contract\Processor;

use Symplify\SmartFileSystem\SmartFileInfo;

interface FileProcessorInterface
{
    public function process(SmartFileInfo $smartFileInfo): string;

    public function supports(SmartFileInfo $smartFileInfo): bool;

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array;
}
