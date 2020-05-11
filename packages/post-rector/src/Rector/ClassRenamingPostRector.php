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

    public function __construct(RenamedClassesCollector $renamedClassesCollector, ClassRenamer $classRenamer)
    {
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->classRenamer = $classRenamer;
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function enterNode(Node $node): ?Node
    {
        return $this->classRenamer->renameNode($node, $this->renamedClassesCollector->getOldToNewClasses());
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Post Rector that renames classes');
    }
}
