<?php

declare(strict_types=1);

namespace Rector\Refactoring\Rector\FileSystem;

use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\PSR4\FileRelocationResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MoveAndRenameClassRector extends AbstractFileSystemRector
{
    /**
     * @var string[]
     */
    private $oldToNewClass = [];

    /**
     * @var FileRelocationResolver
     */
    private $fileRelocationResolver;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @param string[] $oldToNewClass
     */
    public function __construct(
        FileRelocationResolver $fileRelocationResolver,
        RenamedClassesCollector $renamedClassesCollector,
        array $oldToNewClass = []
    ) {
        $this->fileRelocationResolver = $fileRelocationResolver;
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->oldToNewClass = $oldToNewClass;
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $fileNodes = $this->parseFileInfoToNodes($smartFileInfo);
        $fileContent = $smartFileInfo->getContents();

        $class = $this->betterNodeFinder->findFirstClass($fileNodes);
        if ($class === null) {
            return;
        }

        $className = $this->getName($class);

        /** @var string $oldClass */
        foreach ($this->oldToNewClass as $oldClass => $newClass) {
            if ($className !== $oldClass) {
                continue;
            }

            $newFileLocation = $this->fileRelocationResolver->resolveNewFileLocationFromOldClassToNewClass(
                $smartFileInfo,
                $oldClass,
                $newClass
            );

            // create helping rename class rector.yaml + class_alias autoload file
            $this->renamedClassesCollector->addClassRename($oldClass, $newClass);

            $this->moveFile($smartFileInfo, $newFileLocation, $fileContent);
        }
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Move class to respect new location with respect to PSR-4 + follow up with class rename'
        );
    }
}
