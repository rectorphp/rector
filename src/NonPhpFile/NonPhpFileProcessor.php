<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Contract\Rector\NonPhpRectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;

final class NonPhpFileProcessor implements FileProcessorInterface
{
    /**
     * @param NonPhpRectorInterface[] $nonPhpRectors
     */
    public function __construct(
        private array $nonPhpRectors
    ) {
    }

    public function process(File $file, Configuration $configuration): void
    {
        foreach ($this->nonPhpRectors as $nonPhpRector) {
            $newFileContent = $nonPhpRector->refactorFileContent($file->getFileContent());
            $file->changeFileContent($newFileContent);
        }
    }

    public function supports(File $file, Configuration $configuration): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();

        // early assign to variable for increase performance
        // @see https://3v4l.org/FM3vY#focus=8.0.7 vs https://3v4l.org/JZW7b#focus=8.0.7
        $pathname = $smartFileInfo->getPathname();
        // bug in path extension
        foreach ($this->getSupportedFileExtensions() as $fileExtension) {
            if (\str_ends_with($pathname, '.' . $fileExtension)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function getSupportedFileExtensions(): array
    {
        return StaticNonPhpFileSuffixes::SUFFIXES;
    }
}
