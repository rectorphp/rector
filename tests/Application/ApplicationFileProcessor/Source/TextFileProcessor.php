<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor\Source;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Tests\Application\ApplicationFileProcessor\Source\Contract\TextRectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;

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
    public function process(array $files, Configuration $configuration): void
    {
        foreach ($files as $file) {
            $fileContent = $file->getFileContent();

            foreach ($this->textRectors as $textRector) {
                $fileContent = $textRector->refactorContent($fileContent);
            }

            $file->changeFileContent($fileContent);
        }
    }

    public function supports(File $file, Configuration $configuration): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->getSuffix() === 'txt';
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return ['txt'];
    }
}
