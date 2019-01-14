<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\IfMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ConsecutiveNullCompareReturnsToNullCoalesceQueueRector extends AbstractRector
{
    /**
     * @var IfMaintainer
     */
    private $ifMaintainer;

    /**
     * @var Expr[]
     */
    private $coalescingNodes = [];

    /**
     * @var Node[]
     */
    private $nodesToRemove = [];

    public function __construct(IfMaintainer $ifMaintainer)
    {
        $this->ifMaintainer = $ifMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change multiple null compares to ?? queue', [
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
        $this->reset();

        $currentNode = $node;
        while ($currentNode !== null) {
            if ($currentNode instanceof If_) {
                $comparedNode = $this->ifMaintainer->matchIfNotNullReturnValue($currentNode);
                if ($comparedNode) {
                    $this->coalescingNodes[] = $comparedNode;
                    $this->nodesToRemove[] = $currentNode;

                    $currentNode = $currentNode->getAttribute(Attribute::NEXT_NODE);
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

    private function isReturnNull(Node $node): bool
    {
        if (! $node instanceof Return_) {
            return false;
        }

        if ($node->expr === null) {
            return false;
        }

        return $this->isNull($node->expr);
    }

    /**
     * @param Expr[] $coalescingNodes
     */
    private function createReturnCoalesceNode(array $coalescingNodes): Return_
    {
        $coalesceNode = new Coalesce(array_shift($coalescingNodes), array_shift($coalescingNodes));

        foreach ($coalescingNodes as $nextCoalescingNode) {
            $coalesceNode = new Coalesce($coalesceNode, $nextCoalescingNode);
        }

        return new Return_($coalesceNode);
    }

    private function reset(): void
    {
        $this->coalescingNodes = [];
        $this->nodesToRemove = [];
    }
}
