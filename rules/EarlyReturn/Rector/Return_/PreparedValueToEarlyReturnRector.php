<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\While_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\EarlyReturn\ValueObject\BareSingleAssignIf;
use Rector\NodeManipulator\IfManipulator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector\PreparedValueToEarlyReturnRectorTest
 */
final class PreparedValueToEarlyReturnRector extends AbstractRector
{
    /**
     * @readonly
     */
    private IfManipulator $ifManipulator;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(IfManipulator $ifManipulator, BetterNodeFinder $betterNodeFinder)
    {
        $this->ifManipulator = $ifManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Return early prepared value in ifs', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $var = null;

        if (rand(0, 1)) {
            $var = 1;
        }

        if (rand(0, 1)) {
            $var = 2;
        }

        return $var;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (rand(0, 1)) {
            return 1;
        }

        if (rand(0, 1)) {
            return 2;
        }

        return null;
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
    public function refactor(Node $node) : ?StmtsAwareInterface
    {
        if ($node->stmts === null) {
            return null;
        }
        /** @var If_[] $ifs */
        $ifs = [];
        $initialAssign = null;
        $initialAssignPosition = null;
        foreach ($node->stmts as $key => $stmt) {
            if ($stmt instanceof Expression && $stmt->expr instanceof AssignOp) {
                return null;
            }
            if (($stmt instanceof For_ || $stmt instanceof Foreach_ || $stmt instanceof While_ || $stmt instanceof Do_) && $initialAssign instanceof Assign) {
                $isReassignInLoop = (bool) $this->betterNodeFinder->findFirst($stmt, fn(Node $node): bool => $node instanceof Assign && $this->nodeComparator->areNodesEqual($node->var, $initialAssign->var));
                if ($isReassignInLoop) {
                    return null;
                }
            }
            if ($stmt instanceof If_) {
                $ifs[$key] = $stmt;
                continue;
            }
            if ($stmt instanceof Expression && $stmt->expr instanceof Assign) {
                $initialAssign = $stmt->expr;
                $initialAssignPosition = $key;
                $ifs = [];
                continue;
            }
            if (!$stmt instanceof Return_) {
                continue;
            }
            $return = $stmt;
            // match exact variable
            if (!$return->expr instanceof Variable) {
                return null;
            }
            if (!\is_int($initialAssignPosition)) {
                return null;
            }
            if (!$initialAssign instanceof Assign) {
                return null;
            }
            $matchingBareSingleAssignIfs = $this->getMatchingBareSingleAssignIfs($ifs, $node);
            if ($matchingBareSingleAssignIfs === []) {
                return null;
            }
            if (!$this->isVariableSharedInAssignIfsAndReturn($matchingBareSingleAssignIfs, $return->expr, $initialAssign)) {
                return null;
            }
            return $this->refactorToDirectReturns($node, $initialAssignPosition, $matchingBareSingleAssignIfs, $initialAssign, $return);
        }
        return null;
    }
    /**
     * @param If_[] $ifs
     * @return BareSingleAssignIf[]
     */
    private function getMatchingBareSingleAssignIfs(array $ifs, StmtsAwareInterface $stmtsAware) : array
    {
        $bareSingleAssignIfs = [];
        foreach ($ifs as $key => $if) {
            $bareSingleAssignIf = $this->matchBareSingleAssignIf($if, $key, $stmtsAware);
            if (!$bareSingleAssignIf instanceof BareSingleAssignIf) {
                return [];
            }
            $bareSingleAssignIfs[] = $bareSingleAssignIf;
        }
        return $bareSingleAssignIfs;
    }
    /**
     * @param BareSingleAssignIf[] $bareSingleAssignIfs
     */
    private function isVariableSharedInAssignIfsAndReturn(array $bareSingleAssignIfs, Expr $returnedExpr, Assign $initialAssign) : bool
    {
        if (!$this->nodeComparator->areNodesEqual($returnedExpr, $initialAssign->var)) {
            return \false;
        }
        foreach ($bareSingleAssignIfs as $bareSingleAssignIf) {
            $assign = $bareSingleAssignIf->getAssign();
            $isVariableUsed = (bool) $this->betterNodeFinder->findFirst([$bareSingleAssignIf->getIfCondExpr(), $assign->expr], fn(Node $node): bool => $this->nodeComparator->areNodesEqual($node, $returnedExpr));
            if ($isVariableUsed) {
                return \false;
            }
            if (!$this->nodeComparator->areNodesEqual($assign->var, $returnedExpr)) {
                return \false;
            }
        }
        return \true;
    }
    private function matchBareSingleAssignIf(Stmt $stmt, int $key, StmtsAwareInterface $stmtsAware) : ?BareSingleAssignIf
    {
        if (!$stmt instanceof If_) {
            return null;
        }
        // is exactly single stmt
        if (\count($stmt->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $stmt->stmts[0];
        if (!$onlyStmt instanceof Expression) {
            return null;
        }
        $expression = $onlyStmt;
        if (!$expression->expr instanceof Assign) {
            return null;
        }
        if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($stmt)) {
            return null;
        }
        if (!isset($stmtsAware->stmts[$key + 1])) {
            return null;
        }
        if ($stmtsAware->stmts[$key + 1] instanceof If_) {
            return new BareSingleAssignIf($stmt, $expression->expr);
        }
        if ($stmtsAware->stmts[$key + 1] instanceof Return_) {
            return new BareSingleAssignIf($stmt, $expression->expr);
        }
        return null;
    }
    /**
     * @param BareSingleAssignIf[] $bareSingleAssignIfs
     */
    private function refactorToDirectReturns(StmtsAwareInterface $stmtsAware, int $initialAssignPosition, array $bareSingleAssignIfs, Assign $initialAssign, Return_ $return) : StmtsAwareInterface
    {
        // 1. remove initial assign
        unset($stmtsAware->stmts[$initialAssignPosition]);
        // 2. make ifs early return
        foreach ($bareSingleAssignIfs as $bareSingleAssignIf) {
            $if = $bareSingleAssignIf->getIf();
            $if->stmts[0] = new Return_($bareSingleAssignIf->getAssign()->expr);
        }
        // 3. make return default value
        $return->expr = $initialAssign->expr;
        return $stmtsAware;
    }
}
