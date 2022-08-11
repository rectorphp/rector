<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeAnalyzer\VariableAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector\ReturnEarlyIfVariableRectorTest
 */
final class ReturnEarlyIfVariableRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\VariableAnalyzer
     */
    private $variableAnalyzer;
    public function __construct(VariableAnalyzer $variableAnalyzer)
    {
        $this->variableAnalyzer = $variableAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace if conditioned variable override with direct return', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($value)
    {
        if ($value === 50) {
            $value = 100;
        }

        return $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($value)
    {
        if ($value === 50) {
            return 100;
        }

        return $value;
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
        foreach ($stmts as $key => $stmt) {
            $returnVariable = $this->matchNextStmtReturnVariable($node, $key);
            if (!$returnVariable instanceof Variable) {
                continue;
            }
            if ($stmt instanceof If_ && $stmt->else === null && $stmt->elseifs === []) {
                // is single condition if
                $if = $stmt;
                if (\count($if->stmts) !== 1) {
                    continue;
                }
                $onlyIfStmt = $if->stmts[0];
                $assignedExpr = $this->matchOnlyIfStmtReturnExpr($onlyIfStmt, $returnVariable);
                if (!$assignedExpr instanceof Expr) {
                    continue;
                }
                $if->stmts[0] = new Return_($assignedExpr);
                $this->mirrorComments($if->stmts[0], $onlyIfStmt);
                return $node;
            }
        }
        return null;
    }
    private function matchOnlyIfStmtReturnExpr(Stmt $onlyIfStmt, Variable $returnVariable) : ?\PhpParser\Node\Expr
    {
        if (!$onlyIfStmt instanceof Expression) {
            return null;
        }
        if (!$onlyIfStmt->expr instanceof Assign) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($onlyIfStmt);
        if ($phpDocInfo->getVarTagValueNode() instanceof VarTagValueNode) {
            return null;
        }
        $assign = $onlyIfStmt->expr;
        // assign to same variable that is returned
        if (!$assign->var instanceof Variable) {
            return null;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($assign->var)) {
            return null;
        }
        if ($this->variableAnalyzer->isUsedByReference($assign->var)) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($assign->var, $returnVariable)) {
            return null;
        }
        // return directly
        return $assign->expr;
    }
    private function matchNextStmtReturnVariable(StmtsAwareInterface $stmtsAware, int $key) : ?\PhpParser\Node\Expr\Variable
    {
        $nextStmt = $stmtsAware->stmts[$key + 1] ?? null;
        // last item → stop
        if (!$nextStmt instanceof Stmt) {
            return null;
        }
        if (!$nextStmt instanceof Return_) {
            return null;
        }
        // next return must be variable
        if (!$nextStmt->expr instanceof Variable) {
            return null;
        }
        return $nextStmt->expr;
    }
}
