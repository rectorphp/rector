<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\ValueObject\BareSingleAssignIf;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector\PreparedValueToEarlyReturnRectorTest
 */
final class PreparedValueToEarlyReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
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
        /** @var BareSingleAssignIf[] $bareSingleAssignIfs */
        $bareSingleAssignIfs = [];
        $initialAssign = null;
        $initialAssignPosition = null;
        foreach ($node->stmts as $key => $stmt) {
            $bareSingleAssignIf = $this->matchBareSingleAssignIf($stmt, $key, $node);
            if ($bareSingleAssignIf instanceof BareSingleAssignIf) {
                $bareSingleAssignIfs[] = $bareSingleAssignIf;
                continue;
            }
            if ($stmt instanceof Expression && $stmt->expr instanceof Assign) {
                $initialAssign = $stmt->expr;
                $initialAssignPosition = $key;
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
            if ($bareSingleAssignIfs === []) {
                return null;
            }
            if (!$this->isVariableSharedInAssignIfsAndReturn($bareSingleAssignIfs, $return->expr, $initialAssign)) {
                return null;
            }
            return $this->refactorToDirectReturns($node, $initialAssignPosition, $bareSingleAssignIfs, $initialAssign, $return);
        }
        return null;
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
            $isVariableUsed = (bool) $this->betterNodeFinder->findFirst([$bareSingleAssignIf->getIfCondExpr(), $assign->expr], function (Node $node) use($returnedExpr) : bool {
                return $this->nodeComparator->areNodesEqual($node, $returnedExpr);
            });
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
