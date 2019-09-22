<?php declare(strict_types=1);

namespace Rector\Refactoring\Rector\FileSystem;

use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\PSR4\FileRelocationResolver;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class MoveAndRenameClassRector extends AbstractFileSystemRector
{
    /**
     * @var string[]
     */
    private $oldClassToNewClass = [];

    /**
     * @var FileRelocationResolver
     */
    private $fileRelocationResolver;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @param string[] $oldClassToNewClass
     */
    public function __construct(
        FileRelocationResolver $fileRelocationResolver,
        RenamedClassesCollector $renamedClassesCollector,
        array $oldClassToNewClass = []
    ) {
        $this->fileRelocationResolver = $fileRelocationResolver;
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->oldClassToNewClass = $oldClassToNewClass;
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
        foreach ($this->oldClassToNewClass as $oldClass => $newClass) {
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
