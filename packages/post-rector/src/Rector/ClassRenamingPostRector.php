<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\Renaming\NodeManipulator\ClassRenamer;

final class ClassRenamingPostRector extends AbstractPostRector
{
    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @var ClassRenamer
     */
    private $classRenamer;

    public function __construct(ClassRenamer $classRenamer, RenamedClassesCollector $renamedClassesCollector)
    {
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->classRenamer = $classRenamer;
    }

    public function getPriority(): int
    {
        // must be run before name importing, so new names are imported
        return 650;
    }

    public function enterNode(Node $node): ?Node
    {
        $oldToNewClasses = $this->renamedClassesCollector->getOldToNewClasses();
        if ($oldToNewClasses === []) {
            return $node;
        }

        return $this->classRenamer->renameNode($node, $oldToNewClasses);
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Post Rector that renames classes');
    }
}
