<?php

declare (strict_types=1);
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
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.reddit.com/r/PHP/comments/aqk01p/is_there_a_situation_in_which_if_countarray_0/
 * @changelog https://3v4l.org/UCd1b
 *
 * @see \Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\ExplicitBoolCompareRectorTest
 */
final class ExplicitBoolCompareRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;
    public function __construct(StringTypeAnalyzer $stringTypeAnalyzer, ArrayTypeAnalyzer $arrayTypeAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make if conditions more explicit', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [If_::class, ElseIf_::class, Ternary::class];
    }
    /**
     * @param If_|ElseIf_|Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        // skip short ternary
        if ($node instanceof Ternary && $node->if === null) {
            return null;
        }
        if ($node->cond instanceof BooleanNot) {
            $conditionNode = $node->cond->expr;
            $isNegated = \true;
        } else {
            $conditionNode = $node->cond;
            $isNegated = \false;
        }
        if ($conditionNode instanceof Bool_) {
            return null;
        }
        $conditionStaticType = $this->getType($conditionNode);
        if ($conditionStaticType instanceof BooleanType) {
            return null;
        }
        $binaryOp = $this->resolveNewConditionNode($conditionNode, $isNegated);
        if (!$binaryOp instanceof BinaryOp) {
            return null;
        }
        $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
        // avoid duplicated ifs when combined with ChangeOrIfReturnToEarlyReturnRector
        if ($this->shouldSkip($conditionStaticType, $binaryOp, $nextNode)) {
            return null;
        }
        $node->cond = $binaryOp;
        return $node;
    }
    private function shouldSkip(Type $conditionStaticType, BinaryOp $binaryOp, ?Node $nextNode) : bool
    {
        if (!$conditionStaticType->isString()->yes()) {
            return \false;
        }
        if (!$binaryOp instanceof BooleanOr) {
            return \false;
        }
        return !$nextNode instanceof Node;
    }
    private function resolveNewConditionNode(Expr $expr, bool $isNegated) : ?BinaryOp
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
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\Greater|null
     */
    private function resolveCount(bool $isNegated, FuncCall $funcCall)
    {
        $countedType = $this->getType($funcCall->args[0]->value);
        if ($countedType->isArray()->yes()) {
            return null;
        }
        $lNumber = new LNumber(0);
        // compare === 0, assumption
        if ($isNegated) {
            return new Identical($funcCall, $lNumber);
        }
        return new Greater($funcCall, $lNumber);
    }
    /**
     * @return Identical|NotIdentical|null
     */
    private function resolveArray(bool $isNegated, Expr $expr) : ?BinaryOp
    {
        if (!$expr instanceof Variable) {
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
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical|\PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr
     */
    private function resolveString(bool $isNegated, Expr $expr)
    {
        $emptyString = new String_('');
        $identical = $this->resolveIdentical($expr, $isNegated, $emptyString);
        $value = $this->valueResolver->getValue($expr);
        // unknown value. may be from parameter
        if ($value === null) {
            return $this->resolveZeroIdenticalstring($identical, $isNegated, $expr);
        }
        $length = \strlen((string) $value);
        if ($length === 1) {
            $zeroString = new String_('0');
            return $this->resolveIdentical($expr, $isNegated, $zeroString);
        }
        return $identical;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical
     */
    private function resolveIdentical(Expr $expr, bool $isNegated, String_ $string)
    {
        /**
         * // compare === ''
         *
         * @var Identical|NotIdentical $identical
         */
        $identical = $isNegated ? new Identical($expr, $string) : new NotIdentical($expr, $string);
        return $identical;
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $identical
     * @return \PhpParser\Node\Expr\BinaryOp\BooleanAnd|\PhpParser\Node\Expr\BinaryOp\BooleanOr
     */
    private function resolveZeroIdenticalstring($identical, bool $isNegated, Expr $expr)
    {
        $string = new String_('0');
        $zeroIdentical = $isNegated ? new Identical($expr, $string) : new NotIdentical($expr, $string);
        return $isNegated ? new BooleanOr($identical, $zeroIdentical) : new BooleanAnd($identical, $zeroIdentical);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical
     */
    private function resolveInteger(bool $isNegated, Expr $expr)
    {
        $lNumber = new LNumber(0);
        if ($isNegated) {
            return new Identical($expr, $lNumber);
        }
        return new NotIdentical($expr, $lNumber);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical
     */
    private function resolveFloat(bool $isNegated, Expr $expr)
    {
        $dNumber = new DNumber(0.0);
        if ($isNegated) {
            return new Identical($expr, $dNumber);
        }
        return new NotIdentical($expr, $dNumber);
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical
     */
    private function resolveNullable(bool $isNegated, Expr $expr)
    {
        $constFetch = $this->nodeFactory->createNull();
        if ($isNegated) {
            return new Identical($expr, $constFetch);
        }
        return new NotIdentical($expr, $constFetch);
    }
}
