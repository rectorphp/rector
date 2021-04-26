<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor\Source;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Tests\Application\ApplicationFileProcessor\Source\Contract\TextRectorInterface;
use Rector\Core\ValueObject\Application\File;

final class TextFileProcessor implements FileProcessorInterface
{
    /**
     * @var TextRectorInterface[]
     */
    private $textRectors;

    /**
     * @param TextRectorInterface[] $textRectors
     */
    public function __construct(array $textRectors)
    {
        $this->textRectors = $textRectors;
    }

    /**
     * @param File[] $files
     */
    public function process(array $files): void
    {
        foreach ($files as $file) {
            $fileContent = $file->getFileContent();

            foreach ($this->textRectors as $textRector) {
                $fileContent = $textRector->refactorContent($fileContent);
            }

            $file->changeFileContent($fileContent);
        }
    }

    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return ['txt'];
    }

    public function isActive(): bool
    {
        return count($this->textRectors) > 0;
    }
}
