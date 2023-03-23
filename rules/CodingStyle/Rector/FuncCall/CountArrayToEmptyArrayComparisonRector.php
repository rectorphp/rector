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
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        return [FuncCall::class, BooleanNot::class];
    }
    /**
     * @param FuncCall|BooleanNot $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof BooleanNot) {
            return $this->processMarkTruthyNegation($node);
        }
        if (!$this->isName($node, 'count')) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof Arg) {
            return null;
        }
        /** @var Expr $expr */
        $expr = $node->args[0]->value;
        // not pass array type, skip
        if (!$this->isArray($expr)) {
            return null;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            return null;
        }
        $processIdentical = $this->processIdenticalOrNotIdentical($parentNode, $node, $expr);
        if ($processIdentical instanceof Expr) {
            return $processIdentical;
        }
        $processGreaterOrSmaller = $this->processGreaterOrSmaller($parentNode, $node, $expr);
        if ($processGreaterOrSmaller instanceof NotIdentical) {
            return $processGreaterOrSmaller;
        }
        return $this->processMarkTruthy($parentNode, $node, $expr);
    }
    private function processMarkTruthyNegation(BooleanNot $booleanNot) : ?Identical
    {
        if (!$booleanNot->expr instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($booleanNot->expr, 'count')) {
            return null;
        }
        if (!isset($booleanNot->expr->args[0])) {
            return null;
        }
        if (!$booleanNot->expr->args[0] instanceof Arg) {
            return null;
        }
        /** @var Expr $expr */
        $expr = $booleanNot->expr->args[0]->value;
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
    private function processIdenticalOrNotIdentical(Node $node, FuncCall $funcCall, Expr $expr) : ?Expr
    {
        if (($node instanceof Identical || $node instanceof NotIdentical) && $node->right instanceof LNumber && $node->right->value === 0) {
            $this->removeNode($funcCall);
            $node->right = new Array_([]);
            return $expr;
        }
        if (($node instanceof Identical || $node instanceof NotIdentical) && $node->left instanceof LNumber && $node->left->value === 0) {
            $this->removeNode($funcCall);
            $node->left = new Array_([]);
            return $expr;
        }
        return null;
    }
    private function processGreaterOrSmaller(Node $node, FuncCall $funcCall, Expr $expr) : ?NotIdentical
    {
        if ($node instanceof Greater && $node->right instanceof LNumber && $node->right->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($node->right);
            return new NotIdentical($expr, new Array_([]));
        }
        if ($node instanceof Smaller && $node->left instanceof LNumber && $node->left->value === 0) {
            $this->removeNode($funcCall);
            $this->removeNode($node->left);
            return new NotIdentical(new Array_([]), $expr);
        }
        return null;
    }
    private function processMarkTruthy(Node $node, FuncCall $funcCall, Expr $expr) : ?Expr
    {
        if (!$node instanceof If_ && !$node instanceof ElseIf_) {
            return null;
        }
        if ($node->cond === $funcCall) {
            $node->cond = new NotIdentical($expr, new Array_([]));
            return $node->cond;
        }
        return null;
    }
}
