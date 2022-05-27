<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\NodeFactory\InvertedIfFactory;
use Rector\NodeCollector\BinaryOpConditionsCollector;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\ChangeAndIfToEarlyReturnRectorTest
 */
final class ChangeAndIfToEarlyReturnRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeFactory\InvertedIfFactory
     */
    private $invertedIfFactory;
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeCollector\BinaryOpConditionsCollector
     */
    private $binaryOpConditionsCollector;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\EarlyReturn\NodeFactory\InvertedIfFactory $invertedIfFactory, \Rector\NodeNestingScope\ContextAnalyzer $contextAnalyzer, \Rector\NodeCollector\BinaryOpConditionsCollector $binaryOpConditionsCollector)
    {
        $this->ifManipulator = $ifManipulator;
        $this->invertedIfFactory = $invertedIfFactory;
        $this->contextAnalyzer = $contextAnalyzer;
        $this->binaryOpConditionsCollector = $binaryOpConditionsCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes if && to early return', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $car)
    {
        if ($car->hasWheels && $car->hasFuel) {
            return true;
        }

        return false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function canDrive(Car $car)
    {
        if (!$car->hasWheels) {
            return false;
        }

        if (!$car->hasFuel) {
            return false;
        }

        return true;
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
        $stmts = (array) $node->stmts;
        if ($stmts === []) {
            return null;
        }
        $newStmts = [];
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\If_) {
                $newStmts[] = $stmt;
                continue;
            }
            $nextStmt = $stmts[$key + 1] ?? null;
            if ($this->shouldSkip($node, $stmt, $nextStmt)) {
                $newStmts[] = $stmt;
                continue;
            }
            if ($nextStmt instanceof \PhpParser\Node\Stmt\Return_) {
                if ($this->isIfStmtExprUsedInNextReturn($stmt, $nextStmt)) {
                    continue;
                }
                if ($nextStmt->expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
                    continue;
                }
            }
            /** @var BooleanAnd $expr */
            $expr = $stmt->cond;
            $booleanAndConditions = $this->binaryOpConditionsCollector->findConditions($expr, \PhpParser\Node\Expr\BinaryOp\BooleanAnd::class);
            $afterStmts = [];
            if (!$nextStmt instanceof \PhpParser\Node\Stmt\Return_) {
                $afterStmts[] = $stmt->stmts[0];
                $newStmts = \array_merge($newStmts, $this->processReplaceIfs($stmt, $booleanAndConditions, new \PhpParser\Node\Stmt\Return_(), $afterStmts));
                $node->stmts = $newStmts;
                return $node;
            }
            // remove next node
            unset($newStmts[$key + 1]);
            $afterStmts[] = $stmt->stmts[0];
            $ifNextReturnClone = $stmt->stmts[0] instanceof \PhpParser\Node\Stmt\Return_ ? clone $stmt->stmts[0] : new \PhpParser\Node\Stmt\Return_();
            if ($this->isInLoopWithoutContinueOrBreak($stmt)) {
                $afterStmts[] = new \PhpParser\Node\Stmt\Return_();
            }
            $changedStmts = $this->processReplaceIfs($stmt, $booleanAndConditions, $ifNextReturnClone, $afterStmts);
            $changedStmts = \array_merge($newStmts, $changedStmts);
            // update stmts
            $node->stmts = $changedStmts;
            return $node;
        }
        return null;
    }
    private function isInLoopWithoutContinueOrBreak(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        if (!$this->contextAnalyzer->isInLoop($if)) {
            return \false;
        }
        if ($if->stmts[0] instanceof \PhpParser\Node\Stmt\Continue_) {
            return \false;
        }
        return !$if->stmts[0] instanceof \PhpParser\Node\Stmt\Break_;
    }
    /**
     * @param Expr[] $conditions
     * @param Stmt[] $afters
     * @return Stmt[]
     */
    private function processReplaceIfs(\PhpParser\Node\Stmt\If_ $if, array $conditions, \PhpParser\Node\Stmt\Return_ $ifNextReturnClone, array $afters) : array
    {
        $ifs = $this->invertedIfFactory->createFromConditions($if, $conditions, $ifNextReturnClone);
        $this->mirrorComments($ifs[0], $if);
        $result = \array_merge($ifs, $afters);
        if ($if->stmts[0] instanceof \PhpParser\Node\Stmt\Return_) {
            return $result;
        }
        if (!$ifNextReturnClone->expr instanceof \PhpParser\Node\Expr) {
            return $result;
        }
        if ($this->contextAnalyzer->isInLoop($if)) {
            return $result;
        }
        return \array_merge($result, [$ifNextReturnClone]);
    }
    private function shouldSkip(\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface $stmtsAware, \PhpParser\Node\Stmt\If_ $if, ?\PhpParser\Node\Stmt $nexStmt) : bool
    {
        if (!$this->ifManipulator->isIfWithOnlyOneStmt($if)) {
            return \true;
        }
        if (!$if->cond instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
            return \true;
        }
        if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($if)) {
            return \true;
        }
        if ($this->isParentIfReturnsVoidOrParentIfHasNextNode($stmtsAware)) {
            return \true;
        }
        if ($this->isNestedIfInLoop($if, $stmtsAware)) {
            return \true;
        }
        return !$this->isLastIfOrBeforeLastReturn($if, $nexStmt);
    }
    private function isIfStmtExprUsedInNextReturn(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Stmt\Return_ $return) : bool
    {
        if (!$return->expr instanceof \PhpParser\Node\Expr) {
            return \false;
        }
        $ifExprs = $this->betterNodeFinder->findInstanceOf($if->stmts, \PhpParser\Node\Expr::class);
        foreach ($ifExprs as $ifExpr) {
            $isExprFoundInReturn = (bool) $this->betterNodeFinder->findFirst($return->expr, function (\PhpParser\Node $node) use($ifExpr) : bool {
                return $this->nodeComparator->areNodesEqual($node, $ifExpr);
            });
            if ($isExprFoundInReturn) {
                return \true;
            }
        }
        return \false;
    }
    private function isParentIfReturnsVoidOrParentIfHasNextNode(\Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface $stmtsAware) : bool
    {
        if (!$stmtsAware instanceof \PhpParser\Node\Stmt\If_) {
            return \false;
        }
        $nextParent = $stmtsAware->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        return $nextParent instanceof \PhpParser\Node;
    }
    private function isNestedIfInLoop(\PhpParser\Node\Stmt\If_ $if, \Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface $stmtsAware) : bool
    {
        if (!$this->contextAnalyzer->isInLoop($if)) {
            return \false;
        }
        return $stmtsAware instanceof \PhpParser\Node\Stmt\If_ || $stmtsAware instanceof \PhpParser\Node\Stmt\Else_ || $stmtsAware instanceof \PhpParser\Node\Stmt\ElseIf_;
    }
    private function isLastIfOrBeforeLastReturn(\PhpParser\Node\Stmt\If_ $if, ?\PhpParser\Node\Stmt $nextStmt) : bool
    {
        if ($nextStmt instanceof \PhpParser\Node) {
            return $nextStmt instanceof \PhpParser\Node\Stmt\Return_;
        }
        $parent = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return \false;
        }
        if ($parent instanceof \PhpParser\Node\Stmt\If_) {
            return $this->isLastIfOrBeforeLastReturn($parent, $nextStmt);
        }
        return !$this->contextAnalyzer->isHasAssignWithIndirectReturn($parent, $if);
    }
}
