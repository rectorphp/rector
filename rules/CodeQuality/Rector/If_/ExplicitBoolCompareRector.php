<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://www.reddit.com/r/PHP/comments/aqk01p/is_there_a_situation_in_which_if_countarray_0/
 * @see https://3v4l.org/UCd1b
 *
 * @see \Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\ExplicitBoolCompareRectorTest
 */
final class ExplicitBoolCompareRector extends AbstractRector
{
    public function __construct(
        private StringTypeAnalyzer $stringTypeAnalyzer,
        private ArrayTypeAnalyzer $arrayTypeAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make if conditions more explicit', [
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
        if (count($items) === 0) {
            return 'no items';
        }
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

        if ($conditionNode instanceof Bool_) {
            return null;
        }

        $conditionStaticType = $this->getType($conditionNode);
        if ($conditionStaticType instanceof BooleanType || $conditionStaticType instanceof ConstantIntegerType) {
            return null;
        }

        $newConditionNode = $this->resolveNewConditionNode($conditionNode, $isNegated);
        if (! $newConditionNode instanceof BinaryOp) {
            return null;
        }

        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        // avoid duplicated ifs when combined with ChangeOrIfReturnToEarlyReturnRector
        if ($this->shouldSkip($conditionStaticType, $newConditionNode, $nextNode)) {
            return null;
        }

        $node->cond = $newConditionNode;

        return $node;
    }

    private function shouldSkip(Type $conditionStaticType, BinaryOp $binaryOp, ?Node $nextNode): bool
    {
        return $conditionStaticType instanceof StringType && $binaryOp instanceof BooleanOr && ! $nextNode instanceof Node;
    }

    private function resolveNewConditionNode(Expr $expr, bool $isNegated): ?BinaryOp
    {
        if ($expr instanceof FuncCall && $this->nodeNameResolver->isName($expr, 'count')) {
            return $this->resolveCount($isNegated, $expr);
        }

        if ($this->arrayTypeAnalyzer->isArrayType($expr)) {
            return $this->resolveArray($isNegated, $expr);
        }

        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($expr)) {
            return $this->resolveString($isNegated, $expr);
        }

        $exprType = $this->getType($expr);
        if ($exprType instanceof IntegerType) {
            return $this->resolveInteger($isNegated, $expr);
        }

        if ($exprType instanceof FloatType) {
            return $this->resolveFloat($isNegated, $expr);
        }

        if ($this->nodeTypeResolver->isNullableTypeOfSpecificType($expr, ObjectType::class)) {
            return $this->resolveNullable($isNegated, $expr);
        }

        return null;
    }

    private function resolveCount(bool $isNegated, FuncCall $funcCall): Identical | Greater
    {
        $lNumber = new LNumber(0);

        // compare === 0, assumption
        if ($isNegated) {
            return new Identical($funcCall, $lNumber);
        }

        return new Greater($funcCall, $lNumber);
    }

    /**
     * @return Identical|NotIdentical
     */
    private function resolveArray(bool $isNegated, Expr $expr): ?BinaryOp
    {
        if (! $expr instanceof Variable) {
            return null;
        }

        $array = new Array_([]);

        // compare === []
        if ($isNegated) {
            return new Identical($expr, $array);
        }

        return new NotIdentical($expr, $array);
    }

    private function resolveString(bool $isNegated, Expr $expr): Identical | NotIdentical | BooleanAnd | BooleanOr
    {
        $string = new String_('');

        $identical = $this->resolveIdentical($expr, $isNegated, $string);

        $value = $this->valueResolver->getValue($expr);

        // unknown value. may be from parameter
        if ($value === null) {
            return $this->resolveZeroIdenticalstring($identical, $isNegated, $expr);
        }

        $length = strlen($value);

        if ($length === 1) {
            $string = new String_('0');
            return $this->resolveIdentical($expr, $isNegated, $string);
        }

        return $identical;
    }

    private function resolveIdentical(Expr $expr, bool $isNegated, String_ $string): Identical | NotIdentical
    {
        /**
         * // compare === ''
         *
         * @var Identical|NotIdentical $identical
         */
        $identical = $isNegated
            ? new Identical($expr, $string)
            : new NotIdentical($expr, $string);

        return $identical;
    }

    private function resolveZeroIdenticalstring(
        Identical | NotIdentical $identical,
        bool $isNegated,
        Expr $expr
    ): BooleanAnd | BooleanOr {
        $string = new String_('0');
        $zeroIdentical = $isNegated
            ? new Identical($expr, $string)
            : new NotIdentical($expr, $string);

        /**
         * @var BooleanAnd|BooleanOr $result
         */
        $result = $isNegated
            ? new BooleanOr($identical, $zeroIdentical)
            : new BooleanAnd($identical, $zeroIdentical);

        return $result;
    }

    private function resolveInteger(bool $isNegated, Expr $expr): Identical | NotIdentical
    {
        $lNumber = new LNumber(0);

        if ($isNegated) {
            return new Identical($expr, $lNumber);
        }

        return new NotIdentical($expr, $lNumber);
    }

    private function resolveFloat(bool $isNegated, Expr $expr): Identical | NotIdentical
    {
        $dNumber = new DNumber(0.0);

        if ($isNegated) {
            return new Identical($expr, $dNumber);
        }

        return new NotIdentical($expr, $dNumber);
    }

    private function resolveNullable(bool $isNegated, Expr $expr): Identical | NotIdentical
    {
        $constFetch = $this->nodeFactory->createNull();

        if ($isNegated) {
            return new Identical($expr, $constFetch);
        }

        return new NotIdentical($expr, $constFetch);
    }
}
