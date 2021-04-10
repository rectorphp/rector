<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Symplify\SmartFileSystem\SmartFileInfo;

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

    public function process(SmartFileInfo $smartFileInfo): string
    {
        $oldContents = $smartFileInfo->getContents();

        $classRenames = array_merge(
            $this->renamedClassesDataCollector->getOldToNewClasses(),
            $this->renamedClassesCollector->getOldToNewClasses()
        );

        return $this->nonPhpFileClassRenamer->renameClasses($oldContents, $classRenames);
    }

    public function supports(SmartFileInfo $smartFileInfo): bool
    {
        return in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions(), true);
    }

    public function getSupportedFileExtensions(): array
    {
        return StaticNonPhpFileSuffixes::SUFFIXES;
    }
}
