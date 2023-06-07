<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\CountArrayToEmptyArrayComparisonRectorTest
 */
final class CountArrayToEmptyArrayComparisonRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change count array comparison to empty array comparison to improve performance', [new CodeSample(<<<'CODE_SAMPLE'
count($array) === 0;
count($array) > 0;
! count($array);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array === [];
$array !== [];
$array === [];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class, NotIdentical::class, BooleanNot::class, Greater::class, Smaller::class, If_::class, ElseIf_::class];
    }
    /**
     * @param Identical|NotIdentical|BooleanNot|Greater|Smaller|If_|ElseIf_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof BooleanNot) {
            return $this->refactorBooleanNot($node);
        }
        if ($node instanceof Identical || $node instanceof NotIdentical) {
            if ($node->left instanceof FuncCall) {
                $expr = $this->matchCountFuncCallArgExpr($node->left);
            } elseif ($node->right instanceof FuncCall) {
                $expr = $this->matchCountFuncCallArgExpr($node->right);
            } else {
                return null;
            }
            if (!$expr instanceof Expr) {
                return null;
            }
            // not pass array type, skip
            if (!$this->isArray($expr)) {
                return null;
            }
            return $this->refactorIdenticalOrNotIdentical($node, $expr);
        }
        if ($node instanceof Smaller || $node instanceof Greater) {
            return $this->refactorGreaterOrSmaller($node);
        }
        return $this->refactorIfElseIf($node);
    }
    private function refactorBooleanNot(BooleanNot $booleanNot) : ?Identical
    {
        $expr = $this->matchCountFuncCallArgExpr($booleanNot->expr);
        if (!$expr instanceof Expr) {
            return null;
        }
        // not pass array type, skip
        if (!$this->isArray($expr)) {
            return null;
        }
        return new Identical($expr, new Array_([]));
    }
    private function isArray(Expr $expr) : bool
    {
        return $this->getType($expr)->isArray()->yes();
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical|null
     */
    private function refactorIdenticalOrNotIdentical($binaryOp, Expr $expr)
    {
        if ($this->isZeroLNumber($binaryOp->right)) {
            $binaryOp->left = $expr;
            $binaryOp->right = new Array_([]);
            return $binaryOp;
        }
        if ($this->isZeroLNumber($binaryOp->left)) {
            $binaryOp->left = new Array_([]);
            $binaryOp->right = $expr;
            return $binaryOp;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Greater|\PhpParser\Node\Expr\BinaryOp\Smaller $binaryOp
     */
    private function refactorGreaterOrSmaller($binaryOp) : ?\PhpParser\Node\Expr\BinaryOp\NotIdentical
    {
        if ($binaryOp instanceof Greater) {
            $leftExpr = $this->matchCountFuncCallArgExpr($binaryOp->left);
            if (!$leftExpr instanceof Expr) {
                return null;
            }
            if (!$this->isZeroLNumber($binaryOp->right)) {
                return null;
            }
            return new NotIdentical($leftExpr, new Array_([]));
        }
        $rightExpr = $this->matchCountFuncCallArgExpr($binaryOp->right);
        if (!$rightExpr instanceof Expr) {
            return null;
        }
        if (!$this->isZeroLNumber($binaryOp->left)) {
            return null;
        }
        return new NotIdentical(new Array_([]), $rightExpr);
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_ $ifElseIf
     * @return \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_|null
     */
    private function refactorIfElseIf($ifElseIf)
    {
        $expr = $this->matchCountFuncCallArgExpr($ifElseIf->cond);
        if (!$expr instanceof Expr) {
            return null;
        }
        $ifElseIf->cond = new NotIdentical($expr, new Array_([]));
        return $ifElseIf;
    }
    private function matchCountFuncCallArgExpr(Expr $expr) : ?Expr
    {
        if (!$expr instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($expr, 'count')) {
            return null;
        }
        if ($expr->isFirstClassCallable()) {
            return null;
        }
        $firstArg = $expr->getArgs()[0];
        if (!$this->isArray($firstArg->value)) {
            return null;
        }
        return $firstArg->value;
    }
    private function isZeroLNumber(Expr $expr) : bool
    {
        if (!$expr instanceof LNumber) {
            return \false;
        }
        return $expr->value === 0;
    }
}
