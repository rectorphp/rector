<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector\ConsecutiveNullCompareReturnsToNullCoalesceQueueRectorTest
 */
final class ConsecutiveNullCompareReturnsToNullCoalesceQueueRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var Node[]
     */
    private $nodesToRemove = [];
    /**
     * @var Expr[]
     */
    private $coalescingNodes = [];
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change multiple null compares to ?? queue', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (null !== $this->orderItem) {
            return $this->orderItem;
        }

        if (null !== $this->orderItemUnit) {
            return $this->orderItemUnit;
        }

        return null;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return $this->orderItem ?? $this->orderItemUnit;
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->reset();
        $currentNode = $node;
        while ($currentNode !== null) {
            if ($currentNode instanceof \PhpParser\Node\Stmt\If_) {
                $comparedNode = $this->ifManipulator->matchIfNotNullReturnValue($currentNode);
                if ($comparedNode !== null) {
                    $this->coalescingNodes[] = $comparedNode;
                    $this->nodesToRemove[] = $currentNode;
                    $currentNode = $currentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
                    continue;
                }
                return null;
            }
            if ($this->isReturnNull($currentNode)) {
                $this->nodesToRemove[] = $currentNode;
                break;
            }
            return null;
        }
        // at least 2 coalescing nodes are needed
        if (\count($this->coalescingNodes) < 2) {
            return null;
        }
        $this->removeNodes($this->nodesToRemove);
        return $this->createReturnCoalesceNode($this->coalescingNodes);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NULL_COALESCE;
    }
    private function reset() : void
    {
        $this->coalescingNodes = [];
        $this->nodesToRemove = [];
    }
    private function isReturnNull(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\Return_) {
            return \false;
        }
        if ($node->expr === null) {
            return \false;
        }
        return $this->valueResolver->isNull($node->expr);
    }
    /**
     * @param Expr[] $coalescingNodes
     */
    private function createReturnCoalesceNode(array $coalescingNodes) : \PhpParser\Node\Stmt\Return_
    {
        /** @var Expr $left */
        $left = \array_shift($coalescingNodes);
        /** @var Expr $right */
        $right = \array_shift($coalescingNodes);
        $coalesceNode = new \PhpParser\Node\Expr\BinaryOp\Coalesce($left, $right);
        foreach ($coalescingNodes as $coalescingNode) {
            $coalesceNode = new \PhpParser\Node\Expr\BinaryOp\Coalesce($coalesceNode, $coalescingNode);
        }
        return new \PhpParser\Node\Stmt\Return_($coalesceNode);
    }
}
