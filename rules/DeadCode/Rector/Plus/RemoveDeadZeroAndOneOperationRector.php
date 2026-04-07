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
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Scalar\Int_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\Application\File;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector\RemoveDeadZeroAndOneOperationRectorTest
 */
final class RemoveDeadZeroAndOneOperationRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove operation with 1 and 0, that have no effect on the value', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function getNodeTypes(): array
    {
        return [Plus::class, Minus::class, Mul::class, Div::class, AssignPlus::class, AssignMinus::class, AssignMul::class, AssignDiv::class];
    }
    /**
     * @param Plus|Minus|Mul|Div|AssignPlus|AssignMinus|AssignMul|AssignDiv $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof AssignOp) {
            return $this->processAssignOp($node);
        }
        // -, +
        return $this->processBinaryOp($node);
    }
    private function processAssignOp(AssignOp $assignOp): ?Expr
    {
        // +=, -=
        if ($assignOp instanceof AssignPlus || $assignOp instanceof AssignMinus) {
            if (!$this->isLiteralZero($assignOp->expr)) {
                return null;
            }
            if ($this->nodeTypeResolver->isNumberType($assignOp->var)) {
                return $assignOp->var;
            }
        }
        // *, /
        if ($assignOp instanceof AssignMul || $assignOp instanceof AssignDiv) {
            if (!$this->isLiteralOne($assignOp->expr)) {
                return null;
            }
            if ($this->nodeTypeResolver->isNumberType($assignOp->var)) {
                return $assignOp->var;
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
        if ($node instanceof Mul) {
            return $this->processBinaryMulAndDiv($node);
        }
        if ($node instanceof Div) {
            return $this->processBinaryMulAndDiv($node);
        }
        return null;
    }
    private function areNumberType(BinaryOp $binaryOp): bool
    {
        if (!$this->nodeTypeResolver->isNumberType($binaryOp->left)) {
            return \false;
        }
        return $this->nodeTypeResolver->isNumberType($binaryOp->right);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Plus|\PhpParser\Node\Expr\BinaryOp\Minus $binaryOp
     */
    private function processBinaryPlusAndMinus($binaryOp): ?Expr
    {
        if (!$this->areNumberType($binaryOp)) {
            return null;
        }
        if ($this->isLiteralZero($binaryOp->left) && $this->nodeTypeResolver->isNumberType($binaryOp->right)) {
            if ($binaryOp instanceof Minus) {
                return new UnaryMinus($binaryOp->right);
            }
            return $binaryOp->right;
        }
        if (!$this->isLiteralZero($binaryOp->right)) {
            return null;
        }
        return $binaryOp->left;
    }
    private function isMulParenthesized(File $file, Mul $mul): bool
    {
        if (!$mul->right instanceof BinaryOp) {
            return \false;
        }
        $oldTokens = $file->getOldTokens();
        $endTokenPost = $mul->getEndTokenPos();
        if (isset($oldTokens[$endTokenPost]) && (string) $oldTokens[$endTokenPost] === ')') {
            $startTokenPos = $mul->right->getStartTokenPos();
            $previousEndTokenPost = $mul->left->getEndTokenPos();
            while ($startTokenPos > $previousEndTokenPost) {
                --$startTokenPos;
                if (!isset($oldTokens[$startTokenPos])) {
                    return \false;
                }
                // handle space before open parentheses
                if (trim((string) $oldTokens[$startTokenPos]) === '') {
                    continue;
                }
                return (string) $oldTokens[$startTokenPos] === '(';
            }
            return \false;
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Mul|\PhpParser\Node\Expr\BinaryOp\Div $binaryOp
     */
    private function processBinaryMulAndDiv($binaryOp): ?Expr
    {
        if ($binaryOp->left instanceof ClassConstFetch || $binaryOp->right instanceof ClassConstFetch) {
            return null;
        }
        if (!$this->areNumberType($binaryOp)) {
            return null;
        }
        if ($binaryOp instanceof Mul && $this->isLiteralOne($binaryOp->left) && $this->nodeTypeResolver->isNumberType($binaryOp->right)) {
            if ($this->isMulParenthesized($this->getFile(), $binaryOp)) {
                $binaryOp->right->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
            }
            return $binaryOp->right;
        }
        if (!$this->isLiteralOne($binaryOp->right)) {
            return null;
        }
        return $binaryOp->left;
    }
    private function isLiteralOne(Expr $expr): bool
    {
        return $expr instanceof Int_ && $expr->value === 1;
    }
    private function isLiteralZero(Expr $expr): bool
    {
        return $expr instanceof Int_ && $expr->value === 0;
    }
}
