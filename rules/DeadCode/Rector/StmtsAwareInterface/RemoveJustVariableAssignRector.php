<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustVariableAssignRector\RemoveJustVariableAssignRectorTest
 */
final class RemoveJustVariableAssignRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\VariableAnalyzer
     */
    private $variableAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer
     */
    private $exprUsedInNextNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Comment\CommentsMerger
     */
    private $commentsMerger;
    public function __construct(VariableAnalyzer $variableAnalyzer, ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer, CommentsMerger $commentsMerger)
    {
        $this->variableAnalyzer = $variableAnalyzer;
        $this->exprUsedInNextNodeAnalyzer = $exprUsedInNextNodeAnalyzer;
        $this->commentsMerger = $commentsMerger;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove variable just to assign value or return value', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $result = 100;

        $this->temporaryValue = $result;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $this->temporaryValue = 100;
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
        $stmts = (array) $node->stmts;
        if ($stmts === []) {
            return null;
        }
        $originalStmts = $stmts;
        foreach ($stmts as $key => $stmt) {
            $nextStmt = $stmts[$key + 1] ?? null;
            if (!$nextStmt instanceof Stmt) {
                continue;
            }
            $currentAssign = $this->matchExpressionAssign($stmt);
            if (!$currentAssign instanceof Assign) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($stmt);
            if ($phpDocInfo->getVarTagValueNode() instanceof VarTagValueNode) {
                continue;
            }
            $nextAssign = $this->matchExpressionAssign($nextStmt);
            if (!$nextAssign instanceof Assign) {
                continue;
            }
            if ($this->areTooComplexAssignsToShorten($currentAssign, $nextAssign)) {
                continue;
            }
            if (!$this->areTwoVariablesCrossAssign($currentAssign, $nextAssign)) {
                continue;
            }
            // ...
            $currentAssign->var = $nextAssign->var;
            $this->commentsMerger->keepComments($stmt, [$stmts[$key + 1]]);
            unset($stmts[$key + 1]);
        }
        if ($originalStmts === $stmts) {
            return null;
        }
        $node->stmts = $stmts;
        return $node;
    }
    /**
     * This detects if two variables are cross assigned:
     *
     * $<some> = 1000;
     * $this->value = $<some>;
     *
     * + not used $<some> below, so removal will not break it
     */
    private function areTwoVariablesCrossAssign(Assign $currentAssign, Assign $nextAssign) : bool
    {
        // is just re-assign to variable
        if (!$currentAssign->var instanceof Variable) {
            return \false;
        }
        if (!$nextAssign->expr instanceof Variable) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($currentAssign->var, $nextAssign->expr)) {
            return \false;
        }
        if ($this->variableAnalyzer->isUsedByReference($currentAssign->var)) {
            return \false;
        }
        if ($this->variableAnalyzer->isUsedByReference($nextAssign->expr)) {
            return \false;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($currentAssign->var)) {
            return \false;
        }
        return !$this->exprUsedInNextNodeAnalyzer->isUsed($nextAssign->expr);
    }
    /**
     * Shortening should not make code less readable.
     */
    private function areTooComplexAssignsToShorten(Assign $currentAssign, Assign $nextAssign) : bool
    {
        if ($currentAssign->expr instanceof Ternary) {
            return \true;
        }
        if ($currentAssign->expr instanceof Concat) {
            return \true;
        }
        return $nextAssign->var instanceof ArrayDimFetch;
    }
    private function matchExpressionAssign(Stmt $stmt) : ?Assign
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        return $stmt->expr;
    }
}
