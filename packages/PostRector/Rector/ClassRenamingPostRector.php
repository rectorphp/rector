<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Renaming\NodeManipulator\ClassRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class ClassRenamingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\Renaming\NodeManipulator\ClassRenamer
     */
    private $classRenamer;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    public function __construct(ClassRenamer $classRenamer, RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->classRenamer = $classRenamer;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    public function getPriority() : int
    {
        // must be run before name importing, so new names are imported
        return 650;
    }
    public function enterNode(Node $node) : ?Node
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($oldToNewClasses === []) {
            return $node;
        }
        return $this->classRenamer->renameNode($node, $oldToNewClasses);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename references for classes that were renamed during Rector run', [new CodeSample(<<<'CODE_SAMPLE'
function (OriginalClass $someClass)
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function (RenamedClass $someClass)
{
}
CODE_SAMPLE
)]);
    }
}
