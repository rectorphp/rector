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
final class RemoveJustPropertyFetchForAssignRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\JustPropertyFetchVariableAssignMatcher
     */
    private $justPropertyFetchVariableAssignMatcher;
    public function __construct(JustPropertyFetchVariableAssignMatcher $justPropertyFetchVariableAssignMatcher)
    {
        $this->justPropertyFetchVariableAssignMatcher = $justPropertyFetchVariableAssignMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove assign of property, just for value assign', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        $variableAndPropertyFetchAssign = $this->justPropertyFetchVariableAssignMatcher->match($node);
        if (!$variableAndPropertyFetchAssign instanceof VariableAndPropertyFetchAssign) {
            return null;
        }
        $secondStmt = $node->stmts[1];
        if (!$secondStmt instanceof Expression) {
            return null;
        }
        if (!$secondStmt->expr instanceof Assign) {
            return null;
        }
        $middleAssign = $secondStmt->expr;
        $assignVar = $middleAssign->var;
        // unwrap all array dim fetch nesting
        $lastArrayDimFetch = null;
        while ($assignVar instanceof ArrayDimFetch) {
            $lastArrayDimFetch = $assignVar;
            $assignVar = $assignVar->var;
        }
        if (!$assignVar instanceof Variable) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($assignVar, $variableAndPropertyFetchAssign->getVariable())) {
            return null;
        }
        if ($lastArrayDimFetch instanceof ArrayDimFetch) {
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
