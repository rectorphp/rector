<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\If_\NullableCompareToNullRector\NullableCompareToNullRectorTest
 */
final class NullableCompareToNullRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes negate of empty comparison of nullable value to explicit === or !== compare', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->cond instanceof BooleanNot && $this->isNullableNonScalarType($node->cond->expr)) {
            $node->cond = new Identical($node->cond->expr, $this->nodeFactory->createNull());
            return $node;
        }
        if ($this->isNullableNonScalarType($node->cond)) {
            $node->cond = new NotIdentical($node->cond, $this->nodeFactory->createNull());
            return $node;
        }
        return null;
    }
    private function isNullableNonScalarType(Expr $expr) : bool
    {
        $nativeType = $this->nodeTypeResolver->getNativeType($expr);
        // is non-nullable?
        if (!TypeCombinator::containsNull($nativeType)) {
            return \false;
        }
        if (!$nativeType instanceof UnionType) {
            return \false;
        }
        // is array?
        foreach ($nativeType->getTypes() as $subType) {
            if ($subType->isArray()->yes()) {
                return \false;
            }
        }
        $nativeType = TypeCombinator::removeNull($nativeType);
        return !$nativeType->isScalar()->yes();
    }
}
