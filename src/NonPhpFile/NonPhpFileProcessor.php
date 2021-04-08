<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Processor\NonPhpFileProcessorInterface;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Tests\Renaming\Rector\Name\RenameClassRector\RenameNonPhpTest
 */
final class NonPhpFileProcessor implements NonPhpFileProcessorInterface
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

    public function process(SmartFileInfo $smartFileInfo): ?string
    {
        $oldContents = $smartFileInfo->getContents();

        $classRenames = array_merge(
            $this->renamedClassesDataCollector->getOldToNewClasses(),
            $this->renamedClassesCollector->getOldToNewClasses()
        );

        $newContents = $this->nonPhpFileClassRenamer->renameClasses($oldContents, $classRenames);

        // nothing has changed
        if ($oldContents === $newContents) {
            return null;
        }

        return $newContents;
    }

    public function canProcess(SmartFileInfo $smartFileInfo): bool
    {
        return in_array($smartFileInfo->getExtension(), $this->allowedFileExtensions(), true);
    }

    public function allowedFileExtensions(): array
    {
        return StaticNonPhpFileSuffixes::SUFFIXES;
    }
}
