<?php

declare(strict_types=1);

namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\ReturnBinaryOrToEarlyReturnRectorTest
 */
final class ReturnBinaryOrToEarlyReturnRector extends AbstractRector
{
    public function __construct(
        private IfManipulator $ifManipulator,
        private AssignAndBinaryMap $assignAndBinaryMap,
        private CallAnalyzer $callAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes Single return of || to early returns', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept()
    {
        return $this->something() || $this->somethingElse();
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept()
    {
        if ($this->something()) {
            return true;
        }
        return (bool) $this->somethingElse();
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
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof BooleanOr) {
            return null;
        }

        /** @var BooleanOr $booleanOr */
        $booleanOr = $node->expr;

        $left = $booleanOr->left;
        $ifs = $this->createMultipleIfs($left, $node, []);

        if ($ifs === []) {
            return null;
        }

        $this->mirrorComments($ifs[0], $node);
        foreach ($ifs as $if) {
            if (! $this->callAnalyzer->isObjectCall($if->cond)) {
                return null;
            }

            $this->addNodeBeforeNode($if, $node);
        }

        $lastReturnExpr = $this->assignAndBinaryMap->getTruthyExpr($booleanOr->right);
        $this->addNodeBeforeNode(new Return_($lastReturnExpr), $node);
        $this->removeNode($node);

        return $node;
    }

    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function createMultipleIfs(Expr $expr, Return_ $return, array $ifs): array
    {
        while ($expr instanceof BooleanOr) {
            $ifs = array_merge($ifs, $this->collectLeftBooleanOrToIfs($expr, $return, $ifs));
            $ifs[] = $this->ifManipulator->createIfExpr(
                $expr->right,
                new Return_($this->nodeFactory->createTrue())
            );

            $expr = $expr->right;
            if ($expr instanceof BooleanAnd) {
                return [];
            }
            if (! $expr instanceof BooleanOr) {
                continue;
            }
            return [];
        }

        return $ifs + [$this->ifManipulator->createIfExpr($expr, new Return_($this->nodeFactory->createTrue()))];
    }

    /**
     * @param If_[] $ifs
     * @return If_[]
     */
    private function collectLeftBooleanOrToIfs(BooleanOr $BooleanOr, Return_ $return, array $ifs): array
    {
        $left = $BooleanOr->left;
        if (! $left instanceof BooleanOr) {
            return [$this->ifManipulator->createIfExpr($left, new Return_($this->nodeFactory->createTrue()))];
        }

        return $this->createMultipleIfs($left, $return, $ifs);
    }
}
