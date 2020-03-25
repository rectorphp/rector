<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\RemoveUnusedNonEmptyArrayBeforeForeachRectorTest
 */
final class RemoveUnusedNonEmptyArrayBeforeForeachRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused if check to non-empty array before foreach of the array', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $values = [];
        if ($values !== []) {
            foreach ($values as $value) {
                echo $value;
            }
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $values = [];
        foreach ($values as $value) {
            echo $value;
        }
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->ifManipulator->isIfWithOnlyForeach($node)) {
            return null;
        }

        /** @var Foreach_ $foreach */
        $foreach = $node->stmts[0];
        $foreachExpr = $foreach->expr;

        if (! $node->cond instanceof NotIdentical && ! $node->cond instanceof NotEqual) {
            return null;
        }

        /** @var NotIdentical|NotEqual $notIdentical */
        $notIdentical = $node->cond;

        if (! $this->isMatching($notIdentical, $foreachExpr)) {
            return null;
        }

        return $foreach;
    }

    private function isEmptyArray(Expr $expr): bool
    {
        if (! $expr instanceof Array_) {
            return false;
        }

        return $expr->items === [];
    }

    private function isEmptyArrayAndForeachedVariable(Expr $leftExpr, Expr $rightExpr, Expr $foreachExpr): bool
    {
        if (! $this->isEmptyArray($leftExpr)) {
            return false;
        }

        return $this->areNodesWithoutCommentsEqual($foreachExpr, $rightExpr);
    }

    /**
     * @param NotIdentical|NotEqual $binaryOp
     */
    private function isMatching(BinaryOp $binaryOp, Expr $foreachExpr): bool
    {
        if ($this->isEmptyArrayAndForeachedVariable($binaryOp->left, $binaryOp->right, $foreachExpr)) {
            return true;
        }

        return $this->isEmptyArrayAndForeachedVariable($binaryOp->right, $binaryOp->left, $foreachExpr);
    }
}
