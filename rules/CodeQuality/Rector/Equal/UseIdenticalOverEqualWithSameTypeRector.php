<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Equal;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector\UseIdenticalOverEqualWithSameTypeRectorTest
 */
final class UseIdenticalOverEqualWithSameTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use ===/!== over ==/!=, it values have the same type', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Equal::class, NotEqual::class];
    }
    /**
     * @param Equal|NotEqual $node
     */
    public function refactor(Node $node) : ?Node
    {
        $leftStaticType = $this->getType($node->left);
        // objects can be different by content
        if ($leftStaticType instanceof ObjectType) {
            return null;
        }
        if ($leftStaticType instanceof MixedType) {
            return null;
        }
        $rightStaticType = $this->getType($node->right);
        if ($rightStaticType instanceof MixedType) {
            return null;
        }
        // different types
        if (!$leftStaticType->equals($rightStaticType)) {
            return null;
        }
        if ($this->areNonTypedFromParam($node->left, $node->right)) {
            return null;
        }
        if ($node instanceof Equal) {
            return new Identical($node->left, $node->right);
        }
        return new NotIdentical($node->left, $node->right);
    }
    private function areNonTypedFromParam(Expr $left, Expr $right) : bool
    {
        if ($this->exprAnalyzer->isNonTypedFromParam($left)) {
            return \true;
        }
        return $this->exprAnalyzer->isNonTypedFromParam($right);
    }
}
