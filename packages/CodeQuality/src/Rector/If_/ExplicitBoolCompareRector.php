<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.reddit.com/r/PHP/comments/aqk01p/is_there_a_situation_in_which_if_countarray_0/
 * @see https://3v4l.org/UCd1b
 */
final class ExplicitBoolCompareRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Make if conditions more explicit', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run($items)
    {
        if (!count($items)) {
            return 'no items';
        }
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public function run($items)
    {
        if (count($items) < 0) {
            return 'no items';
        }
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
        return [If_::class, ElseIf_::class, Ternary::class];
    }

    /**
     * @param If_|ElseIf_|Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip short ternary
        if ($node instanceof Ternary && $node->if === null) {
            return null;
        }

        if ($node->cond instanceof BooleanNot) {
            $conditionNode = $node->cond->expr;
            $isNegated = true;
        } else {
            $conditionNode = $node->cond;
            $isNegated = false;
        }

        if ($this->isBoolType($conditionNode)) {
            return null;
        }

        $newConditionNode = $this->resolveNewConditionNode($conditionNode, $isNegated);
        if ($newConditionNode === null) {
            return null;
        }

        $node->cond = $newConditionNode;

        return $node;
    }

    private function resolveNewConditionNode(Expr $expr, bool $isNegated): ?BinaryOp
    {
        // various cases
        if ($expr instanceof FuncCall && $this->isName($expr, 'count')) {
            return $this->resolveCount($isNegated, $expr);
        }

        if ($this->isArrayType($expr)) {
            return $this->resolveArray($isNegated, $expr);
        }

        if ($this->isStringyType($expr)) {
            return $this->resolveString($isNegated, $expr);
        }

        if ($this->isIntegerType($expr)) {
            return $this->resolveInteger($isNegated, $expr);
        }

        if ($this->isFloatType($expr)) {
            return $this->resolveFloat($isNegated, $expr);
        }

        if ($this->isNullableObjectType($expr)) {
            return $this->resolveNullable($isNegated, $expr);
        }

        return null;
    }

    /**
     * @return Identical|Greater
     */
    private function resolveCount(bool $isNegated, Expr $expr): BinaryOp
    {
        $valueNode = new LNumber(0);

        // compare === 0, assumption
        if ($isNegated) {
            return new Identical($expr, $valueNode);
        }

        return new Greater($expr, $valueNode);
    }

    /**
     * @return Identical|NotIdentical
     */
    private function resolveArray(bool $isNegated, Expr $expr): BinaryOp
    {
        $valueNode = new Array_([]);

        // compare === []
        if ($isNegated) {
            return new Identical($expr, $valueNode);
        }

        return new NotIdentical($expr, $valueNode);
    }

    /**
     * @return Identical|NotIdentical
     */
    private function resolveString(bool $isNegated, Expr $expr): BinaryOp
    {
        $valueNode = new String_('');

        // compare === ''
        if ($isNegated) {
            return new Identical($expr, $valueNode);
        }

        return new NotIdentical($expr, $valueNode);
    }

    /**
     * @return Identical|NotIdentical
     */
    private function resolveInteger(bool $isNegated, Expr $expr): BinaryOp
    {
        $valueNode = new LNumber(0);

        if ($isNegated) {
            return new Identical($expr, $valueNode);
        }

        return new NotIdentical($expr, $valueNode);
    }

    private function resolveFloat(bool $isNegated, Expr $expr): BinaryOp
    {
        $valueNode = new DNumber(0.0);

        if ($isNegated) {
            return new Identical($expr, $valueNode);
        }

        return new NotIdentical($expr, $valueNode);
    }

    /**
     * @return Identical|NotIdentical
     */
    private function resolveNullable(bool $isNegated, Expr $expr): BinaryOp
    {
        $valueNode = $this->createNull();

        if ($isNegated) {
            return new Identical($expr, $valueNode);
        }

        return new NotIdentical($expr, $valueNode);
    }
}
