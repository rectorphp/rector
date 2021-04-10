<?php

declare(strict_types=1);

namespace Rector\Core\Tests\NonPhpFile\Source;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TextNonPhpFileProcessor implements FileProcessorInterface
{
    public function process(File $file): void
    {
        $oldFileContent = $file->getFileContent();
        $changedFileContent = str_replace('Foo', 'Bar', $oldFileContent);

        $file->changeFileContent($changedFileContent);
    }

    public function supports(File $file): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }

    public function getSupportedFileExtensions(): array
    {
        return ['txt'];
    }
}
