<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\PSR4\Collector\RenamedClassesCollector;

/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameNonPhpTest
 */
final class NonPhpFileProcessor implements FileProcessorInterface
{
    /**
     * @var RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @var NonPhpFileClassRenamer
     */
    private $nonPhpFileClassRenamer;

    public function __construct(
        RenamedClassesDataCollector $renamedClassesDataCollector,
        RenamedClassesCollector $renamedClassesCollector,
        NonPhpFileClassRenamer $nonPhpFileClassRenamer
    ) {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->nonPhpFileClassRenamer = $nonPhpFileClassRenamer;
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
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }

    public function getSupportedFileExtensions(): array
    {
        return StaticNonPhpFileSuffixes::SUFFIXES;
    }

    private function processFile(File $file): void
    {
        $fileInfo = $file->getSmartFileInfo();
        $oldFileContents = $fileInfo->getContents();

        $classRenames = array_merge(
            $this->renamedClassesDataCollector->getOldToNewClasses(),
            $this->renamedClassesCollector->getOldToNewClasses()
        );

        $changedFileContents = $this->nonPhpFileClassRenamer->renameClasses($oldFileContents, $classRenames);
        $file->changeFileContent($changedFileContents);
    }
}
