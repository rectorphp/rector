<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\EarlyReturn\NodeFactory\InvertedIfFactory;
use Rector\NodeCollector\NodeAnalyzer\BooleanAndAnalyzer;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\ChangeAndIfToEarlyReturnRectorTest
 */
final class ChangeAndIfToEarlyReturnRector extends AbstractRector
{
    public function __construct(
        private IfManipulator $ifManipulator,
        private InvertedIfFactory $invertedIfFactory,
        private ContextAnalyzer $contextAnalyzer,
        private BooleanAndAnalyzer $booleanAndAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes if && to early return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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

                ,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     * @return Node|Node[]|null
     */
    public function refactor(Node $node)
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $ifNextReturn = $this->getIfNextReturn($node);
        if ($ifNextReturn instanceof Return_ && $this->isIfStmtExprUsedInNextReturn($node, $ifNextReturn)) {
            return null;
        }

        /** @var BooleanAnd $expr */
        $expr = $node->cond;
        $booleanAndConditions = $this->booleanAndAnalyzer->findBooleanAndConditions($expr);

        if (! $ifNextReturn instanceof Return_) {
            $this->addNodeAfterNode($node->stmts[0], $node);
            return $this->processReplaceIfs($node, $booleanAndConditions, new Return_());
        }

        if ($ifNextReturn instanceof Return_ && $ifNextReturn->expr instanceof BooleanAnd) {
            return null;
        }

        $this->removeNode($ifNextReturn);
        $ifNextReturn = $node->stmts[0];
        $this->addNodeAfterNode($ifNextReturn, $node);

        $ifNextReturnClone = $ifNextReturn instanceof Return_
            ? clone $ifNextReturn
            : new Return_();

        if (! $this->contextAnalyzer->isInLoop($node)) {
            return $this->processReplaceIfs($node, $booleanAndConditions, $ifNextReturnClone);
        }

        if (! $ifNextReturn instanceof Expression) {
            return null;
        }

        $this->addNodeAfterNode(new Return_(), $node);
        return $this->processReplaceIfs($node, $booleanAndConditions, $ifNextReturnClone);
    }

    /**
     * @param Expr[] $conditions
     * @return If_|Node[]
     */
    private function processReplaceIfs(If_ $node, array $conditions, Return_ $ifNextReturnClone)
    {
        $ifs = $this->invertedIfFactory->createFromConditions($node, $conditions, $ifNextReturnClone);
        $this->mirrorComments($ifs[0], $node);

        foreach ($ifs as $if) {
            $this->addNodeBeforeNode($if, $node);
        }

        $this->removeNode($node);

        if (! $node->stmts[0] instanceof Return_ && $ifNextReturnClone->expr instanceof Expr) {
            return [$node, $ifNextReturnClone];
        }

        return $node;
    }

    private function shouldSkip(If_ $if): bool
    {
        if (! $this->ifManipulator->isIfWithOnlyOneStmt($if)) {
            return true;
        }
        if (! $if->cond instanceof BooleanAnd) {
            return true;
        }
        if (! $this->ifManipulator->isIfWithoutElseAndElseIfs($if)) {
            return true;
        }

        if ($this->isParentIfReturnsVoidOrParentIfHasNextNode($if)) {
            return true;
        }

        if ($this->isNestedIfInLoop($if)) {
            return true;
        }

        return ! $this->isLastIfOrBeforeLastReturn($if);
    }

    private function isIfStmtExprUsedInNextReturn(If_ $if, Return_ $return): bool
    {
        if (! $return->expr instanceof Expr) {
            return false;
        }

        $ifExprs = $this->betterNodeFinder->findInstanceOf($if->stmts, Expr::class);
        foreach ($ifExprs as $ifExpr) {
            $isExprFoundInReturn = (bool) $this->betterNodeFinder->findFirst(
                $return->expr,
                fn (Node $node): bool => $this->nodeComparator->areNodesEqual($node, $ifExpr)
            );
            if ($isExprFoundInReturn) {
                return true;
            }
        }

        return false;
    }

    private function getIfNextReturn(If_ $if): ?Return_
    {
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if (! $nextNode instanceof Return_) {
            return null;
        }

        return $nextNode;
    }

    private function isParentIfReturnsVoidOrParentIfHasNextNode(If_ $if): bool
    {
        $parentNode = $if->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof If_) {
            return false;
        }

        $nextParent = $parentNode->getAttribute(AttributeKey::NEXT_NODE);
        return $nextParent instanceof Node;
    }

    private function isNestedIfInLoop(If_ $if): bool
    {
        if (! $this->contextAnalyzer->isInLoop($if)) {
            return false;
        }

        return (bool) $this->betterNodeFinder->findParentTypes($if, [If_::class, Else_::class, ElseIf_::class]);
    }

    private function isLastIfOrBeforeLastReturn(If_ $if): bool
    {
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextNode instanceof Node) {
            return $nextNode instanceof Return_;
        }

        $parent = $if->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof If_) {
            return $this->isLastIfOrBeforeLastReturn($parent);
        }

        return ! $this->contextAnalyzer->isHasAssignWithIndirectReturn($parent, $if);
    }
}
