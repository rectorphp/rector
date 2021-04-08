<?php
declare(strict_types=1);

namespace Rector\Core\Contract\Processor;

use Symplify\SmartFileSystem\SmartFileInfo;

interface NonPhpFileProcessorInterface
{
    public function process(SmartFileInfo $smartFileInfo): ?string;

    public function canProcess(SmartFileInfo $smartFileInfo): bool;

    public function transformOldContent(SmartFileInfo $smartFileInfo): string;

    /**
     * @return string[]
     */
    public function allowedFileExtensions(): array;
}
