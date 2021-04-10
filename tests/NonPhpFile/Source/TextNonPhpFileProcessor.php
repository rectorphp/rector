<?php

declare(strict_types=1);

namespace Rector\Core\Tests\NonPhpFile\Source;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TextNonPhpFileProcessor implements FileProcessorInterface
{
    public function process(SmartFileInfo $smartFileInfo): string
    {
        $oldContent = $smartFileInfo->getContents();
        return str_replace('Foo', 'Bar', $oldContent);
    }

    public function supports(SmartFileInfo $smartFileInfo): bool
    {
        return in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions());
    }

    public function getSupportedFileExtensions(): array
    {
        return ['txt'];
    }
}
