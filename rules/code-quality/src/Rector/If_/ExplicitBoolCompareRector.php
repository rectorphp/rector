<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.reddit.com/r/PHP/comments/aqk01p/is_there_a_situation_in_which_if_countarray_0/
 * @see https://3v4l.org/UCd1b
 *
 * @see \Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\ExplicitBoolCompareRectorTest
 */
final class ExplicitBoolCompareRector extends AbstractRector
{
    /**
     * @var StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;

    /**
     * @var ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;

    public function __construct(StringTypeAnalyzer $stringTypeAnalyzer, ArrayTypeAnalyzer $arrayTypeAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
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

        if ($conditionNode instanceof Bool_) {
            return null;
        }

        $conditionStaticType = $this->getStaticType($conditionNode);
        if ($conditionStaticType instanceof BooleanType) {
            return null;
        }

        $newConditionNode = $this->resolveNewConditionNode($conditionNode, $isNegated);
        if (! $newConditionNode instanceof BinaryOp) {
            return null;
        }

        $node->cond = $newConditionNode;

        return $node;
    }

    private function resolveNewConditionNode(Expr $expr, bool $isNegated): ?BinaryOp
    {
        // various cases
        if ($this->isFuncCallName($expr, 'count')) {
            return $this->resolveCount($isNegated, $expr);
        }

        if ($this->arrayTypeAnalyzer->isArrayType($expr)) {
            return $this->resolveArray($isNegated, $expr);
        }

        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($expr)) {
            return $this->resolveString($isNegated, $expr);
        }

        if ($this->isStaticType($expr, IntegerType::class)) {
            return $this->resolveInteger($isNegated, $expr);
        }

        if ($this->isStaticType($expr, FloatType::class)) {
            return $this->resolveFloat($isNegated, $expr);
        }

        if ($this->nodeTypeResolver->isNullableObjectType($expr)) {
            return $this->resolveNullable($isNegated, $expr);
        }

        return null;
    }

    /**
     * @return Identical|Greater
     */
    private function resolveCount(bool $isNegated, Expr $expr): BinaryOp
    {
        $lNumber = new LNumber(0);

        // compare === 0, assumption
        if ($isNegated) {
            return new Identical($expr, $lNumber);
        }

        return new Greater($expr, $lNumber);
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

    /**
     * @return Identical|NotIdentical
     */
    private function resolveString(bool $isNegated, Expr $expr): BinaryOp
    {
        $string = new String_('');

        // compare === ''
        if ($isNegated) {
            return new Identical($expr, $string);
        }

        return new NotIdentical($expr, $string);
    }

    /**
     * @return Identical|NotIdentical
     */
    private function resolveInteger(bool $isNegated, Expr $expr): BinaryOp
    {
        $lNumber = new LNumber(0);

        if ($isNegated) {
            return new Identical($expr, $lNumber);
        }

        return new NotIdentical($expr, $lNumber);
    }

    private function resolveFloat(bool $isNegated, Expr $expr): BinaryOp
    {
        $dNumber = new DNumber(0.0);

        if ($isNegated) {
            return new Identical($expr, $dNumber);
        }

        return new NotIdentical($expr, $dNumber);
    }

    /**
     * @return Identical|NotIdentical
     */
    private function resolveNullable(bool $isNegated, Expr $expr): BinaryOp
    {
        $constFetch = $this->nodeFactory->createNull();

        if ($isNegated) {
            return new Identical($expr, $constFetch);
        }

        return new NotIdentical($expr, $constFetch);
    }
}
