<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor\Source;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;

final class TextFileProcessor implements FileProcessorInterface
{
    /**
     * @param File[] $files
     */
    public function process(array $files): void
    {
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }

    public function supports(File $file): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return ['txt'];
    }

    private function processFile($file): void
    {
        $oldFileContent = $file->getFileContent();
        $changedFileContent = str_replace('Foo', 'Bar', $oldFileContent);

        $file->changeFileContent($changedFileContent);
    }
}
