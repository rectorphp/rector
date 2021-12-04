<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Application\ApplicationFileProcessor\Source;

use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Tests\Application\ApplicationFileProcessor\Source\Contract\TextRectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;

final class TextFileProcessor implements FileProcessorInterface
{
    /**
     * @var TextRectorInterface[]
     */
    private $textRectors;

    /**
     * @param TextRectorInterface[] $textRectors
     */
    public function __construct(
        private FileDiffFactory $fileDiffFactory,
        array $textRectors,
    ) {
        $this->textRectors = $textRectors;
    }

    /**
     * @return array{file_diffs: FileDiff[], system_errors: SystemError[]}
     */
    public function process(File $file, Configuration $configuration): array
    {
        $originalFileContent = $file->getFileContent();

        $fileContent = $originalFileContent;
        foreach ($this->textRectors as $textRector) {
            $fileContent = $textRector->refactorContent($fileContent);
        }

        $fileDiff = null;
        if ($fileContent !== $originalFileContent) {
            $file->changeFileContent($fileContent);

            $fileDiff = $this->fileDiffFactory->createFileDiff(
                $file,
                $file->getOriginalFileContent(),
                $file->getFileContent()
            );

            $file->setFileDiff($fileDiff);
        }

        return [
            Bridge::FILE_DIFFS => $fileDiff ? [$fileDiff] : [],
            Bridge::SYSTEM_ERRORS => [],
        ];
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
