<?php

declare(strict_types=1);

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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector\ConsecutiveNullCompareReturnsToNullCoalesceQueueRectorTest
 */
final class ConsecutiveNullCompareReturnsToNullCoalesceQueueRector extends AbstractRector
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
     * @var IfManipulator
     */
    private $ifManipulator;

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change multiple null compares to ?? queue', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return $this->orderItem ?? $this->orderItemUnit;
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
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::NULL_COALESCE)) {
            return null;
        }

        $this->reset();

        $currentNode = $node;
        while ($currentNode !== null) {
            if ($currentNode instanceof If_) {
                $comparedNode = $this->ifManipulator->matchIfNotNullReturnValue($currentNode);
                if ($comparedNode !== null) {
                    $this->coalescingNodes[] = $comparedNode;
                    $this->nodesToRemove[] = $currentNode;

                    $currentNode = $currentNode->getAttribute(AttributeKey::NEXT_NODE);
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
        if (count($this->coalescingNodes) < 2) {
            return null;
        }
        $this->removeNodes($this->nodesToRemove);

        return $this->createReturnCoalesceNode($this->coalescingNodes);
    }

    private function reset(): void
    {
        $this->coalescingNodes = [];
        $this->nodesToRemove = [];
    }

    private function isReturnNull(Node $node): bool
    {
        if (! $node instanceof Return_) {
            return false;
        }

        if ($node->expr === null) {
            return false;
        }

        return $this->valueResolver->isNull($node->expr);
    }

    /**
     * @param Expr[] $coalescingNodes
     */
    private function createReturnCoalesceNode(array $coalescingNodes): Return_
    {
        /** @var Expr $left */
        $left = array_shift($coalescingNodes);

        /** @var Expr $right */
        $right = array_shift($coalescingNodes);

        $coalesceNode = new Coalesce($left, $right);
        foreach ($coalescingNodes as $nextCoalescingNode) {
            $coalesceNode = new Coalesce($coalesceNode, $nextCoalescingNode);
        }

        return new Return_($coalesceNode);
    }
}
