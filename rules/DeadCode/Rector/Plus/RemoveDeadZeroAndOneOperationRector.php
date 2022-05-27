<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Plus;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignOp\Div as AssignDiv;
use PhpParser\Node\Expr\AssignOp\Minus as AssignMinus;
use PhpParser\Node\Expr\AssignOp\Mul as AssignMul;
use PhpParser\Node\Expr\AssignOp\Plus as AssignPlus;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\UnaryMinus;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/I0BGs
 *
 * @see \Rector\Tests\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector\RemoveDeadZeroAndOneOperationRectorTest
 */
final class RemoveDeadZeroAndOneOperationRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove operation with 1 and 0, that have no effect on the value', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5 * 1;
        $value = 5 + 0;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5;
        $value = 5;
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
        return [\PhpParser\Node\Expr\BinaryOp\Plus::class, \PhpParser\Node\Expr\BinaryOp\Minus::class, \PhpParser\Node\Expr\BinaryOp\Mul::class, \PhpParser\Node\Expr\BinaryOp\Div::class, \PhpParser\Node\Expr\AssignOp\Plus::class, \PhpParser\Node\Expr\AssignOp\Minus::class, \PhpParser\Node\Expr\AssignOp\Mul::class, \PhpParser\Node\Expr\AssignOp\Div::class];
    }
    /**
     * @param Plus|Minus|Mul|Div|AssignPlus|AssignMinus|AssignMul|AssignDiv $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\AssignOp) {
            return $this->processAssignOp($node);
        }
        // -, +
        return $this->processBinaryOp($node);
    }
    private function processAssignOp(\PhpParser\Node\Expr\AssignOp $assignOp) : ?\PhpParser\Node\Expr
    {
        // +=, -=
        if ($assignOp instanceof \PhpParser\Node\Expr\AssignOp\Plus || $assignOp instanceof \PhpParser\Node\Expr\AssignOp\Minus) {
            if (!$this->valueResolver->isValue($assignOp->expr, 0)) {
                return null;
            }
            if ($this->nodeTypeResolver->isNumberType($assignOp->var)) {
                return $assignOp->var;
            }
        }
        // *, /
        if ($assignOp instanceof \PhpParser\Node\Expr\AssignOp\Mul || $assignOp instanceof \PhpParser\Node\Expr\AssignOp\Div) {
            if (!$this->valueResolver->isValue($assignOp->expr, 1)) {
                return null;
            }
            if ($this->nodeTypeResolver->isNumberType($assignOp->var)) {
                return $assignOp->var;
            }
        }
        return null;
    }
    private function processBinaryOp(\PhpParser\Node $node) : ?\PhpParser\Node\Expr
    {
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Plus || $node instanceof \PhpParser\Node\Expr\BinaryOp\Minus) {
            return $this->processBinaryPlusAndMinus($node);
        }
        // *, /
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Mul) {
            return $this->processBinaryMulAndDiv($node);
        }
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Div) {
            return $this->processBinaryMulAndDiv($node);
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Plus|\PhpParser\Node\Expr\BinaryOp\Minus $binaryOp
     */
    private function processBinaryPlusAndMinus($binaryOp) : ?\PhpParser\Node\Expr
    {
        if ($this->valueResolver->isValue($binaryOp->left, 0) && $this->nodeTypeResolver->isNumberType($binaryOp->right)) {
            if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Minus) {
                return new \PhpParser\Node\Expr\UnaryMinus($binaryOp->right);
            }
            return $binaryOp->right;
        }
        if (!$this->valueResolver->isValue($binaryOp->right, 0)) {
            return null;
        }
        if (!$this->nodeTypeResolver->isNumberType($binaryOp->left)) {
            return null;
        }
        return $binaryOp->left;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Mul|\PhpParser\Node\Expr\BinaryOp\Div $binaryOp
     */
    private function processBinaryMulAndDiv($binaryOp) : ?\PhpParser\Node\Expr
    {
        if ($binaryOp instanceof \PhpParser\Node\Expr\BinaryOp\Mul && $this->valueResolver->isValue($binaryOp->left, 1) && $this->nodeTypeResolver->isNumberType($binaryOp->right)) {
            return $binaryOp->right;
        }
        if (!$this->valueResolver->isValue($binaryOp->right, 1)) {
            return null;
        }
        if (!$this->nodeTypeResolver->isNumberType($binaryOp->left)) {
            return null;
        }
        return $binaryOp->left;
    }
}
