<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\If_\NullableCompareToNullRector\NullableCompareToNullRectorTest
 */
final class NullableCompareToNullRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes negate of empty comparison of nullable value to explicit === or !== compare', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
/** @var stdClass|null $value */
if ($value) {
}

if (!$value) {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var stdClass|null $value */
if ($value !== null) {
}

if ($value === null) {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->cond instanceof \PhpParser\Node\Expr\BooleanNot && $this->isNullableNonScalarType($node->cond->expr)) {
            $node->cond = new \PhpParser\Node\Expr\BinaryOp\Identical($node->cond->expr, $this->nodeFactory->createNull());
            return $node;
        }
        if ($this->isNullableNonScalarType($node->cond)) {
            $node->cond = new \PhpParser\Node\Expr\BinaryOp\NotIdentical($node->cond, $this->nodeFactory->createNull());
            return $node;
        }
        return null;
    }
    private function isNullableNonScalarType(\PhpParser\Node $node) : bool
    {
        $staticType = $this->getType($node);
        if ($staticType instanceof \PHPStan\Type\MixedType) {
            return \false;
        }
        if (!$staticType instanceof \PHPStan\Type\UnionType) {
            return \false;
        }
        // is non-nullable?
        if ($staticType->isSuperTypeOf(new \PHPStan\Type\NullType())->no()) {
            return \false;
        }
        // is array?
        foreach ($staticType->getTypes() as $subType) {
            if ($subType instanceof \PHPStan\Type\ArrayType) {
                return \false;
            }
        }
        // is string?
        if ($staticType->isSuperTypeOf(new \PHPStan\Type\StringType())->yes()) {
            return \false;
        }
        // is number?
        if ($staticType->isSuperTypeOf(new \PHPStan\Type\IntegerType())->yes()) {
            return \false;
        }
        // is bool?
        if ($staticType->isSuperTypeOf(new \PHPStan\Type\BooleanType())->yes()) {
            return \false;
        }
        return !$staticType->isSuperTypeOf(new \PHPStan\Type\FloatType())->yes();
    }
}
