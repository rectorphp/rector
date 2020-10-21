<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Plus;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignOp\Div as AssignDiv;
use PhpParser\Node\Expr\AssignOp\Minus as AssignMinus;
use PhpParser\Node\Expr\AssignOp\Mul as AssignMul;
use PhpParser\Node\Expr\AssignOp\Plus as AssignPlus;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\UnaryMinus;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/I0BGs
 *
 * @see \Rector\DeadCode\Tests\Rector\Plus\RemoveDeadZeroAndOneOperationRector\RemoveDeadZeroAndOneOperationRectorTest
 */
final class RemoveDeadZeroAndOneOperationRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove operation with 1 and 0, that have no effect on the value', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5 * 1;
        $value = 5 + 0;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5;
        $value = 5;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [
            Plus::class,
            Minus::class,
            Mul::class,
            Div::class,
            AssignPlus::class,
            AssignMinus::class,
            AssignMul::class,
            AssignDiv::class,
        ];
    }

    /**
     * @param Plus|Minus|Mul|Div|AssignPlus|AssignMinus|AssignMul|AssignDiv $node
     */
    public function refactor(Node $node): ?Node
    {
        $changedNode = null;
        $previousNode = $node;

        if ($node instanceof AssignOp) {
            $changedNode = $this->processAssignOp($node);
        }

        // -, +
        if ($node instanceof BinaryOp) {
            $changedNode = $this->processBinaryOp($node);
        }

        // recurse nested combinations
        while ($changedNode !== null && ! $this->areNodesEqual($previousNode, $changedNode)) {
            $previousNode = $changedNode;

            if ($changedNode instanceof BinaryOp || $changedNode instanceof AssignOp) {
                $changedNode = $this->refactor($changedNode);
            }

            // nothing more to change, return last node
            if ($changedNode === null) {
                return $previousNode;
            }
        }

        return $changedNode;
    }

    private function processAssignOp(Node $node): ?Expr
    {
        // +=, -=
        if ($node instanceof AssignPlus || $node instanceof AssignMinus) {
            if (! $this->isValue($node->expr, 0)) {
                return null;
            }

            if ($this->isNumberType($node->var)) {
                return $node->var;
            }
        }

        // *, /
        if ($node instanceof AssignMul || $node instanceof AssignDiv) {
            if (! $this->isValue($node->expr, 1)) {
                return null;
            }
            if ($this->isNumberType($node->var)) {
                return $node->var;
            }
        }

        return null;
    }

    private function processBinaryOp(Node $node): ?Expr
    {
        if ($node instanceof Plus || $node instanceof Minus) {
            return $this->processBinaryPlusAndMinus($node);
        }

        // *, /
        if ($node instanceof Mul || $node instanceof Div) {
            return $this->processBinaryMulAndDiv($node);
        }

        return null;
    }

    /**
     * @param Plus|Minus $binaryOp
     */
    private function processBinaryPlusAndMinus(BinaryOp $binaryOp): ?Expr
    {
        if ($this->isValue($binaryOp->left, 0) && $this->isNumberType($binaryOp->right)) {
            if ($binaryOp instanceof Minus) {
                return new UnaryMinus($binaryOp->right);
            }
            return $binaryOp->right;
        }

        if ($this->isValue($binaryOp->right, 0) && $this->isNumberType($binaryOp->left)) {
            return $binaryOp->left;
        }

        return null;
    }

    /**
     * @param Mul|Div $binaryOp
     */
    private function processBinaryMulAndDiv(BinaryOp $binaryOp): ?Expr
    {
        if ($binaryOp instanceof Mul && $this->isValue($binaryOp->left, 1) && $this->isNumberType($binaryOp->right)) {
            return $binaryOp->right;
        }

        if ($this->isValue($binaryOp->right, 1) && $this->isNumberType($binaryOp->left)) {
            return $binaryOp->left;
        }

        return null;
    }
}
