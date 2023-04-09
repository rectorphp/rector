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
use Rector\EarlyReturn\NodeAnalyzer\IfAndAnalyzer;
use Rector\EarlyReturn\NodeAnalyzer\SimpleScalarAnalyzer;
use Rector\EarlyReturn\NodeFactory\InvertedIfFactory;
use Rector\NodeCollector\BinaryOpConditionsCollector;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\ChangeAndIfToEarlyReturnRectorTest
 */
final class ChangeAndIfToEarlyReturnRector extends AbstractRector
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
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeAnalyzer\SimpleScalarAnalyzer
     */
    private $simpleScalarAnalyzer;
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeAnalyzer\IfAndAnalyzer
     */
    private $ifAndAnalyzer;
    public function __construct(IfManipulator $ifManipulator, InvertedIfFactory $invertedIfFactory, ContextAnalyzer $contextAnalyzer, BinaryOpConditionsCollector $binaryOpConditionsCollector, SimpleScalarAnalyzer $simpleScalarAnalyzer, IfAndAnalyzer $ifAndAnalyzer)
    {
        $this->ifManipulator = $ifManipulator;
        $this->invertedIfFactory = $invertedIfFactory;
        $this->contextAnalyzer = $contextAnalyzer;
        $this->binaryOpConditionsCollector = $binaryOpConditionsCollector;
        $this->simpleScalarAnalyzer = $simpleScalarAnalyzer;
        $this->ifAndAnalyzer = $ifAndAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes if && to early return', [new CodeSample(<<<'CODE_SAMPLE'
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
        if (! $car->hasWheels) {
            return false;
        }

        if (! $car->hasFuel) {
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
        $newStmts = [];
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                // keep natural original order
                $newStmts[] = $stmt;
                continue;
            }
            $nextStmt = $stmts[$key + 1] ?? null;
            if ($this->shouldSkip($node, $stmt, $nextStmt)) {
                $newStmts[] = $stmt;
                continue;
            }
            if ($nextStmt instanceof Return_) {
                if ($this->ifAndAnalyzer->isIfStmtExprUsedInNextReturn($stmt, $nextStmt)) {
                    continue;
                }
                if ($nextStmt->expr instanceof BooleanAnd) {
                    continue;
                }
            }
            /** @var BooleanAnd $expr */
            $expr = $stmt->cond;
            $booleanAndConditions = $this->binaryOpConditionsCollector->findConditions($expr, BooleanAnd::class);
            $afterStmts = [];
            if (!$nextStmt instanceof Return_) {
                $afterStmts[] = $stmt->stmts[0];
                $node->stmts = \array_merge($newStmts, $this->processReplaceIfs($stmt, $booleanAndConditions, new Return_(), $afterStmts, $nextStmt));
                return $node;
            }
            // remove next node
            unset($newStmts[$key + 1]);
            $afterStmts[] = $stmt->stmts[0];
            $ifNextReturnClone = $stmt->stmts[0] instanceof Return_ ? clone $stmt->stmts[0] : new Return_();
            if ($this->isInLoopWithoutContinueOrBreak($stmt)) {
                $afterStmts[] = new Return_();
            }
            $changedStmts = $this->processReplaceIfs($stmt, $booleanAndConditions, $ifNextReturnClone, $afterStmts, $nextStmt);
            // update stmts
            $node->stmts = \array_merge($newStmts, $changedStmts);
            return $node;
        }
        return null;
    }
    private function isInLoopWithoutContinueOrBreak(If_ $if) : bool
    {
        if (!$this->contextAnalyzer->isInLoop($if)) {
            return \false;
        }
        if ($if->stmts[0] instanceof Continue_) {
            return \false;
        }
        return !$if->stmts[0] instanceof Break_;
    }
    /**
     * @param Expr[] $conditions
     * @param Stmt[] $afters
     * @return Stmt[]
     */
    private function processReplaceIfs(If_ $if, array $conditions, Return_ $ifNextReturnClone, array $afters, ?Stmt $nextStmt) : array
    {
        $ifs = $this->invertedIfFactory->createFromConditions($if, $conditions, $ifNextReturnClone, $nextStmt);
        $this->mirrorComments($ifs[0], $if);
        $result = \array_merge($ifs, $afters);
        if ($if->stmts[0] instanceof Return_) {
            return $result;
        }
        if (!$ifNextReturnClone->expr instanceof Expr) {
            return $result;
        }
        if ($this->contextAnalyzer->isInLoop($if)) {
            return $result;
        }
        return \array_merge($result, [$ifNextReturnClone]);
    }
    private function shouldSkip(StmtsAwareInterface $stmtsAware, If_ $if, ?Stmt $nexStmt) : bool
    {
        if (!$this->ifManipulator->isIfWithOnlyOneStmt($if)) {
            return \true;
        }
        if (!$if->cond instanceof BooleanAnd) {
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
        // is simple return? skip it
        $onlyStmt = $if->stmts[0];
        if ($onlyStmt instanceof Return_ && $onlyStmt->expr instanceof Expr && $this->simpleScalarAnalyzer->isSimpleScalar($onlyStmt->expr)) {
            return \true;
        }
        if ($this->ifAndAnalyzer->isIfAndWithInstanceof($if->cond)) {
            return \true;
        }
        return !$this->isLastIfOrBeforeLastReturn($if, $nexStmt);
    }
    private function isParentIfReturnsVoidOrParentIfHasNextNode(StmtsAwareInterface $stmtsAware) : bool
    {
        if (!$stmtsAware instanceof If_) {
            $parent = $stmtsAware->getAttribute(AttributeKey::PARENT_NODE);
            if ($parent instanceof If_) {
                $node = $parent->getAttribute(AttributeKey::NEXT_NODE);
                return !$node instanceof Return_;
            }
            return \false;
        }
        $nextNode = $stmtsAware->getAttribute(AttributeKey::NEXT_NODE);
        return $nextNode instanceof Node;
    }
    private function isNestedIfInLoop(If_ $if, StmtsAwareInterface $stmtsAware) : bool
    {
        if (!$this->contextAnalyzer->isInLoop($if)) {
            return \false;
        }
        return $stmtsAware instanceof If_ || $stmtsAware instanceof Else_ || $stmtsAware instanceof ElseIf_;
    }
    private function isLastIfOrBeforeLastReturn(If_ $if, ?Stmt $nextStmt) : bool
    {
        if ($nextStmt instanceof Node) {
            return $nextStmt instanceof Return_;
        }
        $parentNode = $if->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            return \false;
        }
        if ($parentNode instanceof If_) {
            return $this->isLastIfOrBeforeLastReturn($parentNode, $nextStmt);
        }
        return !$this->contextAnalyzer->hasAssignWithIndirectReturn($parentNode, $if);
    }
}
