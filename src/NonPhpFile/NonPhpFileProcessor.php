<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Nette\Utils\Strings;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Contract\Rector\NonPhpRectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;

/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameNonPhpTest
 */
final class NonPhpFileProcessor implements FileProcessorInterface
{
    /**
     * @param NonPhpRectorInterface[] $nonPhpRectors
     */
    public function __construct(
        private array $nonPhpRectors
    ) {
    }

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
        $smartFileInfo = $file->getSmartFileInfo();

        // bug in path extension
        foreach ($this->getSupportedFileExtensions() as $supportedFileExtension) {
            if (Strings::endsWith($smartFileInfo->getPathname(), '.' . $supportedFileExtension)) {
                return true;
            }
        }

        return false;
    }

    public function getSupportedFileExtensions(): array
    {
        return StaticNonPhpFileSuffixes::SUFFIXES;
    }

    private function processFile(File $file): void
    {
        foreach ($this->nonPhpRectors as $nonPhpRector) {
            $newFileContent = $nonPhpRector->refactorFileContent($file->getFileContent());
            $file->changeFileContent($newFileContent);
        }
    }
}
