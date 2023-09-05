<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Ternary\NumberCompareToMaxFuncCallRector\NumberCompareToMaxFuncCallRectorTest
 */
final class NumberCompareToMaxFuncCallRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Ternary number compare to max() call', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return $value > 100 ? $value : 100;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return max($value, 100);
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
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->cond instanceof BinaryOp) {
            return null;
        }
        $binaryOp = $node->cond;
        if (!$this->areIntegersCompared($binaryOp)) {
            return null;
        }
        if ($binaryOp instanceof Smaller || $binaryOp instanceof SmallerOrEqual) {
            if (!$this->nodeComparator->areNodesEqual($binaryOp->left, $node->else)) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($binaryOp->right, $node->if)) {
                return null;
            }
            return $this->nodeFactory->createFuncCall('max', [$node->if, $node->else]);
        }
        if ($binaryOp instanceof Greater || $binaryOp instanceof GreaterOrEqual) {
            if (!$this->nodeComparator->areNodesEqual($binaryOp->left, $node->if)) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($binaryOp->right, $node->else)) {
                return null;
            }
            return $this->nodeFactory->createFuncCall('max', [$node->if, $node->else]);
        }
        return null;
    }
    private function areIntegersCompared(BinaryOp $binaryOp) : bool
    {
        $leftType = $this->getType($binaryOp->left);
        if (!$leftType->isInteger()->yes()) {
            return \false;
        }
        $rightType = $this->getType($binaryOp->right);
        return $rightType->isInteger()->yes();
    }
}
