<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\JustPropertyFetchVariableAssignMatcher;
use Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchForAssignRector\RemoveJustPropertyFetchForAssignRectorTest
 */
final class RemoveJustPropertyFetchForAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\JustPropertyFetchVariableAssignMatcher
     */
    private $justPropertyFetchVariableAssignMatcher;
    public function __construct(\Rector\DeadCode\NodeAnalyzer\JustPropertyFetchVariableAssignMatcher $justPropertyFetchVariableAssignMatcher)
    {
        $this->justPropertyFetchVariableAssignMatcher = $justPropertyFetchVariableAssignMatcher;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove assign of property, just for value assign', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $items = [];

    public function run()
    {
        $items = $this->items;
        $items[] = 1000;
        $this->items = $items ;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $items = [];

    public function run()
    {
        $this->items[] = 1000;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $variableAndPropertyFetchAssign = $this->justPropertyFetchVariableAssignMatcher->match($node);
        if (!$variableAndPropertyFetchAssign instanceof \Rector\DeadCode\ValueObject\VariableAndPropertyFetchAssign) {
            return null;
        }
        $secondStmt = $node->stmts[1];
        if (!$secondStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!$secondStmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $middleAssign = $secondStmt->expr;
        $assignVar = $middleAssign->var;
        // unwrap all array dim fetch nesting
        $lastArrayDimFetch = null;
        while ($assignVar instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $lastArrayDimFetch = $assignVar;
            $assignVar = $assignVar->var;
        }
        if (!$assignVar instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($assignVar, $variableAndPropertyFetchAssign->getVariable())) {
            return null;
        }
        if ($lastArrayDimFetch instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $lastArrayDimFetch->var = $variableAndPropertyFetchAssign->getPropertyFetch();
        } else {
            $middleAssign->var = $variableAndPropertyFetchAssign->getPropertyFetch();
        }
        // remove just-assign stmts
        unset($node->stmts[0]);
        unset($node->stmts[2]);
        return $node;
    }
}
