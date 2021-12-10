<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Equal;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector\UseIdenticalOverEqualWithSameTypeRectorTest
 */
final class UseIdenticalOverEqualWithSameTypeRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use ===/!== over ==/!=, it values have the same type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue == $secondValue;
         $isDiffernt = $firstValue != $secondValue;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue === $secondValue;
         $isDiffernt = $firstValue !== $secondValue;
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
        return [\PhpParser\Node\Expr\BinaryOp\Equal::class, \PhpParser\Node\Expr\BinaryOp\NotEqual::class];
    }
    /**
     * @param Equal|NotEqual $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $leftStaticType = $this->getType($node->left);
        $rightStaticType = $this->getType($node->right);
        // objects can be different by content
        if ($leftStaticType instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        if ($leftStaticType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        if ($rightStaticType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        // different types
        if (!$leftStaticType->equals($rightStaticType)) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Equal) {
            return new \PhpParser\Node\Expr\BinaryOp\Identical($node->left, $node->right);
        }
        return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($node->left, $node->right);
    }
}
