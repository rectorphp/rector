<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Analyser\Scope;
use Rector\CodingStyle\Application\UseImportsRemover;
use Rector\Core\Configuration\Option;
use Rector\Core\Configuration\Parameter\SimpleParameterProvider;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    /**
     * @readonly
     * @var \Rector\CodingStyle\Application\UseImportsRemover
     */
    private $useImportsRemover;
    /**
     * @var \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace|\PhpParser\Node\Stmt\Namespace_|null
     */
    private $rootNode = null;
    public function __construct(ClassRenamer $classRenamer, RenamedClassesDataCollector $renamedClassesDataCollector, UseImportsRemover $useImportsRemover)
    {
        $this->classRenamer = $classRenamer;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->useImportsRemover = $useImportsRemover;
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function beforeTraverse(array $nodes) : array
    {
        // ensure reset early on every run to avoid reuse existing value
        $this->rootNode = null;
        foreach ($nodes as $node) {
            if ($node instanceof FileWithoutNamespace || $node instanceof Namespace_) {
                $this->rootNode = $node;
                break;
            }
        }
        return $nodes;
    }
    public function enterNode(Node $node) : ?Node
    {
        // cannot be renamed
        if ($node instanceof Expr || $node instanceof Arg || $node instanceof PropertyProperty) {
            return null;
        }
        $oldToNewClasses = $this->renamedClassesDataCollector->getOldToNewClasses();
        if ($oldToNewClasses === []) {
            return null;
        }
        /** @var Scope|null $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $result = $this->classRenamer->renameNode($node, $oldToNewClasses, $scope);
        if (!SimpleParameterProvider::provideBoolParameter(Option::AUTO_IMPORT_NAMES)) {
            return $result;
        }
        if (!$this->rootNode instanceof FileWithoutNamespace && !$this->rootNode instanceof Namespace_) {
            return $result;
        }
        $removedUses = $this->renamedClassesDataCollector->getOldClasses();
        $this->rootNode->stmts = $this->useImportsRemover->removeImportsFromStmts($this->rootNode->stmts, $removedUses);
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
