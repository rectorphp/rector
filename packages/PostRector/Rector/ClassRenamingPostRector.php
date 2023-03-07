<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Core\Configuration\RectorConfigProvider;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\NonPhpFile\Rector\RenameClassNonPhpRector;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\PostRector\Contract\Rector\PostRectorDependencyInterface;
use Rector\Renaming\NodeManipulator\ClassRenamer;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class ClassRenamingPostRector extends \Rector\PostRector\Rector\AbstractPostRector implements PostRectorDependencyInterface
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
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RectorConfigProvider
     */
    private $rectorConfigProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Application\UseImportsRemover
     */
    private $useImportsRemover;
    public function __construct(ClassRenamer $classRenamer, RenamedClassesDataCollector $renamedClassesDataCollector, RectorConfigProvider $rectorConfigProvider, BetterNodeFinder $betterNodeFinder, UseImportsRemover $useImportsRemover)
    {
        $this->classRenamer = $classRenamer;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->rectorConfigProvider = $rectorConfigProvider;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useImportsRemover = $useImportsRemover;
    }
    public function getPriority() : int
    {
        // must be run before name importing, so new names are imported
        return 650;
    }
    /**
     * @return class-string<RectorInterface>[]
     */
    public function getRectorDependencies() : array
    {
        return [RenameClassRector::class, RenameClassNonPhpRector::class];
    }
    public function enterNode(Node $node) : ?Node
    {
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($oldToNewClasses === []) {
            return null;
        }
        $result = $this->classRenamer->renameNode($node, $oldToNewClasses);
        if (!$this->rectorConfigProvider->shouldImportNames()) {
            return $result;
        }
        $rootNode = $this->betterNodeFinder->findParentByTypes($node, [Namespace_::class, FileWithoutNamespace::class]);
        if (!$rootNode instanceof Node) {
            return $result;
        }
        $removedUses = $this->renamedClassesDataCollector->getOldClasses();
        $this->useImportsRemover->removeImportsFromStmts($rootNode->stmts, $removedUses);
        return $result;
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
