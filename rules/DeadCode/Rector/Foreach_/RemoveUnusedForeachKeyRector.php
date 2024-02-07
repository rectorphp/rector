<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\NodeFinder;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector\RemoveUnusedForeachKeyRectorTest
 */
final class RemoveUnusedForeachKeyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    public function __construct(NodeFinder $nodeFinder, StmtsManipulator $stmtsManipulator)
    {
        $this->nodeFinder = $nodeFinder;
        $this->stmtsManipulator = $stmtsManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused key in foreach', [new CodeSample(<<<'CODE_SAMPLE'
$items = [];
foreach ($items as $key => $value) {
    $result = $value;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$items = [];
foreach ($items as $value) {
    $result = $value;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Foreach_) {
                continue;
            }
            if (!$stmt->keyVar instanceof Variable) {
                continue;
            }
            $keyVar = $stmt->keyVar;
            $isNodeUsed = (bool) $this->nodeFinder->findFirst($stmt->stmts, function (Node $node) use($keyVar) : bool {
                return $this->nodeComparator->areNodesEqual($node, $keyVar);
            });
            if ($isNodeUsed) {
                continue;
            }
            if ($this->stmtsManipulator->isVariableUsedInNextStmt($node, $key + 1, (string) $this->getName($keyVar))) {
                continue;
            }
            $stmt->keyVar = null;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
