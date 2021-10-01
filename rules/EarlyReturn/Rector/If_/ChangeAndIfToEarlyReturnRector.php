<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\NodeFactory\InvertedIfFactory;
use Rector\NodeCollector\NodeAnalyzer\BooleanAndAnalyzer;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeNestingScope\ParentFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\ChangeAndIfToEarlyReturnRectorTest
 */
final class ChangeAndIfToEarlyReturnRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @var \Rector\EarlyReturn\NodeFactory\InvertedIfFactory
     */
    private $invertedIfFactory;
    /**
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    /**
     * @var \Rector\NodeCollector\NodeAnalyzer\BooleanAndAnalyzer
     */
    private $booleanAndAnalyzer;
    /**
     * @var \Rector\NodeNestingScope\ParentFinder
     */
    private $parentFinder;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\EarlyReturn\NodeFactory\InvertedIfFactory $invertedIfFactory, \Rector\NodeNestingScope\ContextAnalyzer $contextAnalyzer, \Rector\NodeCollector\NodeAnalyzer\BooleanAndAnalyzer $booleanAndAnalyzer, \Rector\NodeNestingScope\ParentFinder $parentFinder)
    {
        $this->ifManipulator = $ifManipulator;
        $this->invertedIfFactory = $invertedIfFactory;
        $this->contextAnalyzer = $contextAnalyzer;
        $this->booleanAndAnalyzer = $booleanAndAnalyzer;
        $this->parentFinder = $parentFinder;
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     * @return Node[]|null
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $ifNextReturn = $this->getIfNextReturn($node);
        if ($ifNextReturn instanceof \PhpParser\Node\Stmt\Return_ && $this->isIfStmtExprUsedInNextReturn($node, $ifNextReturn)) {
            return null;
        }
        if ($ifNextReturn instanceof \PhpParser\Node\Stmt\Return_ && $ifNextReturn->expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd) {
            return null;
        }
        /** @var BooleanAnd $expr */
        $expr = $node->cond;
        $booleanAndConditions = $this->booleanAndAnalyzer->findBooleanAndConditions($expr);
        $afters = [];
        if (!$ifNextReturn instanceof \PhpParser\Node\Stmt\Return_) {
            $afters[] = $node->stmts[0];
            return $this->processReplaceIfs($node, $booleanAndConditions, new \PhpParser\Node\Stmt\Return_(), $afters);
        }
        $this->removeNode($ifNextReturn);
        $afters[] = $node->stmts[0];
        $ifNextReturnClone = $node->stmts[0] instanceof \PhpParser\Node\Stmt\Return_ ? clone $node->stmts[0] : new \PhpParser\Node\Stmt\Return_();
        if ($this->isInLoopWithoutContinueOrBreak($node)) {
            $afters[] = new \PhpParser\Node\Stmt\Return_();
        }
        return $this->processReplaceIfs($node, $booleanAndConditions, $ifNextReturnClone, $afters);
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
     * @param Node[] $afters
     * @return Node[]
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
    private function shouldSkip(\PhpParser\Node\Stmt\If_ $if) : bool
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
        if ($this->isParentIfReturnsVoidOrParentIfHasNextNode($if)) {
            return \true;
        }
        if ($this->isNestedIfInLoop($if)) {
            return \true;
        }
        return !$this->isLastIfOrBeforeLastReturn($if);
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
    private function getIfNextReturn(\PhpParser\Node\Stmt\If_ $if) : ?\PhpParser\Node\Stmt\Return_
    {
        $nextNode = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        return $nextNode;
    }
    private function isParentIfReturnsVoidOrParentIfHasNextNode(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $parentNode = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Stmt\If_) {
            return \false;
        }
        $nextParent = $parentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        return $nextParent instanceof \PhpParser\Node;
    }
    private function isNestedIfInLoop(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        if (!$this->contextAnalyzer->isInLoop($if)) {
            return \false;
        }
        return (bool) $this->parentFinder->findByTypes($if, [\PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\Else_::class, \PhpParser\Node\Stmt\ElseIf_::class]);
    }
    private function isLastIfOrBeforeLastReturn(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $nextNode = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if ($nextNode instanceof \PhpParser\Node) {
            return $nextNode instanceof \PhpParser\Node\Stmt\Return_;
        }
        $parent = $if->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return \false;
        }
        if ($parent instanceof \PhpParser\Node\Stmt\If_) {
            return $this->isLastIfOrBeforeLastReturn($parent);
        }
        return !$this->contextAnalyzer->isHasAssignWithIndirectReturn($parent, $if);
    }
}
