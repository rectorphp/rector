<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Equal;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PHPStan\Type\MixedType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector\UseIdenticalOverEqualWithSameTypeRectorTest
 */
final class UseIdenticalOverEqualWithSameTypeRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use ===/!== over ==/!=, it values have the same type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue == $secondValue;
         $isDifferent = $firstValue != $secondValue;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $firstValue, int $secondValue)
    {
         $isSame = $firstValue === $secondValue;
         $isDifferent = $firstValue !== $secondValue;
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
        return [Equal::class, NotEqual::class];
    }
    /**
     * @param Equal|NotEqual $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->left instanceof ArrayDimFetch || $node->right instanceof ArrayDimFetch) {
            return null;
        }
        $leftStaticType = $this->nodeTypeResolver->getNativeType($node->left);
        $rightStaticType = $this->nodeTypeResolver->getNativeType($node->right);
        // objects can be different by content
        if (!$leftStaticType->isObject()->no() || !$rightStaticType->isObject()->no()) {
            return null;
        }
        if ($leftStaticType instanceof MixedType || $rightStaticType instanceof MixedType) {
            return null;
        }
        if ($leftStaticType->isString()->yes() && $rightStaticType->isString()->yes()) {
            return $this->processIdenticalOrNotIdentical($node);
        }
        // different types
        if (!$leftStaticType->equals($rightStaticType)) {
            return null;
        }
        return $this->processIdenticalOrNotIdentical($node);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Equal|\PhpParser\Node\Expr\BinaryOp\NotEqual $node
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical
     */
    private function processIdenticalOrNotIdentical($node)
    {
        if ($node instanceof Equal) {
            return new Identical($node->left, $node->right);
        }
        return new NotIdentical($node->left, $node->right);
    }
}
