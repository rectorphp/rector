<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
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
use PHPStan\Type\ArrayType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector\CountArrayToEmptyArrayComparisonRectorTest
 */
final class CountArrayToEmptyArrayComparisonRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change count array comparison to empty array comparison to improve performance', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\BooleanNot::class];
    }
    /**
     * @param FuncCall|BooleanNot $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\BooleanNot) {
            return $this->processMarkTruthyNegation($node);
        }
        if (!$this->isName($node, 'count')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        /** @var Expr $expr */
        $expr = $node->args[0]->value;
        // not pass array type, skip
        if (!$this->isArray($expr)) {
            return null;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parent instanceof \PhpParser\Node) {
            return null;
        }
        $processIdentical = $this->processIdenticalOrNotIdentical($parent, $node, $expr);
        if ($processIdentical !== null) {
            return $processIdentical;
        }
        $processGreaterOrSmaller = $this->processGreaterOrSmaller($parent, $node, $expr);
        if ($processGreaterOrSmaller !== null) {
            return $processGreaterOrSmaller;
        }
        return $this->processMarkTruthy($parent, $node, $expr);
    }
    private function processMarkTruthyNegation(\PhpParser\Node\Expr\BooleanNot $booleanNot) : ?\PhpParser\Node\Expr\BinaryOp\Identical
    {
        if (!$booleanNot->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return null;
        }
        if (!$this->isName($booleanNot->expr, 'count')) {
            return null;
        }
        if (!isset($booleanNot->expr->args[0])) {
            return null;
        }
        if (!$booleanNot->expr->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        /** @var Expr $expr */
        $expr = $booleanNot->expr->args[0]->value;
        // not pass array type, skip
        if (!$this->isArray($expr)) {
            return null;
        }
        return new \PhpParser\Node\Expr\BinaryOp\Identical($expr, new \PhpParser\Node\Expr\Array_([]));
    }
    private function isArray(\PhpParser\Node\Expr $expr) : bool
    {
        return $this->getType($expr) instanceof \PHPStan\Type\ArrayType;
    }
    private function processIdenticalOrNotIdentical(\PhpParser\Node $node, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr
    {
        if (($node instanceof \PhpParser\Node\Expr\BinaryOp\Identical || $node instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) && $node->right instanceof \PhpParser\Node\Scalar\LNumber && $node->right->value === 0) {
            $this->removeNode($funcCall);
            $node->right = new \PhpParser\Node\Expr\Array_([]);
            return $expr;
        }
        if (($node instanceof \PhpParser\Node\Expr\BinaryOp\Identical || $node instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) && $node->left instanceof \PhpParser\Node\Scalar\LNumber && $node->left->value === 0) {
            $this->removeNode($funcCall);
            $node->left = new \PhpParser\Node\Expr\Array_([]);
            return $expr;
        }
        return null;
    }
    private function processGreaterOrSmaller(\PhpParser\Node $node, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr\BinaryOp\NotIdentical
    {
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Greater && $node->right instanceof \PhpParser\Node\Scalar\LNumber && $node->right->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($node->right);
            return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr, new \PhpParser\Node\Expr\Array_([]));
        }
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Smaller && $node->left instanceof \PhpParser\Node\Scalar\LNumber && $node->left->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($node->left);
            return new \PhpParser\Node\Expr\BinaryOp\NotIdentical(new \PhpParser\Node\Expr\Array_([]), $expr);
        }
        return null;
    }
    private function processMarkTruthy(\PhpParser\Node $node, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr
    {
        if (!$node instanceof \PhpParser\Node\Stmt\If_ && !$node instanceof \PhpParser\Node\Stmt\ElseIf_) {
            return null;
        }
        if ($node->cond === $funcCall) {
            $node->cond = new \PhpParser\Node\Expr\BinaryOp\NotIdentical($expr, new \PhpParser\Node\Expr\Array_([]));
            return $node->cond;
        }
        return null;
    }
}
