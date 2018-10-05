<?php declare(strict_types=1);

namespace Rector\Php\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use Rector\Printer\BetterStandardPrinter;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class TernaryToNullCoalescingRector extends AbstractRector
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

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
     * @param Ternary $ternaryNode
     */
    public function refactor(Node $ternaryNode): ?Node
    {
        if ($ternaryNode->cond instanceof Isset_) {
            $coalesceNode = $this->processTernaryWithIsset($ternaryNode);
            if ($coalesceNode) {
                return $coalesceNode;
            }
        }

        if ($ternaryNode->cond instanceof Identical) {
            [$checkedNode, $fallbackNode] = [$ternaryNode->else, $ternaryNode->if];
        } elseif ($ternaryNode->cond instanceof NotIdentical) {
            [$checkedNode, $fallbackNode] = [$ternaryNode->if, $ternaryNode->else];
        } else {
            // not a match
            return $ternaryNode;
        }

        /** @var Identical|NotIdentical $ternaryCompareNode */
        $ternaryCompareNode = $ternaryNode->cond;

        if ($this->isNullMatch($ternaryCompareNode->left, $ternaryCompareNode->right, $checkedNode)) {
            return new Coalesce($checkedNode, $fallbackNode);
        }

        if ($this->isNullMatch($ternaryCompareNode->right, $ternaryCompareNode->left, $checkedNode)) {
            return new Coalesce($checkedNode, $fallbackNode);
        }

        return $ternaryNode;
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

        $ifContent = $this->betterStandardPrinter->prettyPrint([$ternaryNode->if]);
        $varNodeContent = $this->betterStandardPrinter->prettyPrint([$issetNode->vars[0]]);

        if ($ifContent === $varNodeContent) {
            return new Coalesce($ternaryNode->if, $ternaryNode->else);
        }

        return null;
    }

    private function isNullMatch(Node $possibleNullNode, Node $firstNode, Node $secondNode): bool
    {
        if (! $this->isNullConstant($possibleNullNode)) {
            return false;
        }

        $firstNodeContent = $this->betterStandardPrinter->prettyPrint([$firstNode]);
        $secondNodeContent = $this->betterStandardPrinter->prettyPrint([$secondNode]);

        return $firstNodeContent === $secondNodeContent;
    }

    private function isNullConstant(Node $node): bool
    {
        if (! $node instanceof ConstFetch) {
            return false;
        }

        return $node->name->toLowerString() === 'null';
    }
}
