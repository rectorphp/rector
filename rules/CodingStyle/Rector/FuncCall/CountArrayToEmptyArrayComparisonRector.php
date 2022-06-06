<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Greater;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Smaller;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\LNumber;
use RectorPrefix20220606\PhpParser\Node\Stmt\ElseIf_;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof Node) {
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
        return $this->getType($expr) instanceof ArrayType;
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
