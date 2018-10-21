<?php declare(strict_types=1);

namespace Rector\Php\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class TernaryToNullCoalescingRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes unneeded null check to ?? operator',
            [
                new CodeSample('$value === null ? 10 : $value;', '$value ?? 10;'),
                new CodeSample('isset($value) ? $value : 10;', '$value ?? 10;'),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->cond instanceof Isset_) {
            $coalesceNode = $this->processTernaryWithIsset($node);
            if ($coalesceNode) {
                return $coalesceNode;
            }
        }

        if ($node->cond instanceof Identical) {
            [$checkedNode, $fallbackNode] = [$node->else, $node->if];
        } elseif ($node->cond instanceof NotIdentical) {
            [$checkedNode, $fallbackNode] = [$node->if, $node->else];
        } else {
            // not a match
            return null;
        }

        /** @var Identical|NotIdentical $ternaryCompareNode */
        $ternaryCompareNode = $node->cond;

        if ($this->isNullMatch($ternaryCompareNode->left, $ternaryCompareNode->right, $checkedNode)) {
            return new Coalesce($checkedNode, $fallbackNode);
        }

        if ($this->isNullMatch($ternaryCompareNode->right, $ternaryCompareNode->left, $checkedNode)) {
            return new Coalesce($checkedNode, $fallbackNode);
        }

        return $node;
    }

    private function processTernaryWithIsset(Ternary $ternaryNode): ?Coalesce
    {
        /** @var Isset_ $issetNode */
        $issetNode = $ternaryNode->cond;

        // none or multiple isset values cannot be handled here
        if (! isset($issetNode->vars[0]) || count($issetNode->vars) > 1) {
            return null;
        }

        if ($ternaryNode->if === null) {
            return null;
        }

        if ($this->areNodesEqual($ternaryNode->if, $issetNode->vars[0])) {
            return new Coalesce($ternaryNode->if, $ternaryNode->else);
        }

        return null;
    }

    private function isNullMatch(Node $possibleNullNode, Node $firstNode, Node $secondNode): bool
    {
        if (! $this->isNullConstant($possibleNullNode)) {
            return false;
        }

        return $this->areNodesEqual($firstNode, $secondNode);
    }

    private function isNullConstant(Node $node): bool
    {
        if (! $node instanceof ConstFetch) {
            return false;
        }

        return $node->name->toLowerString() === 'null';
    }
}
