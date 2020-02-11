<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;

final class ConcatManipulator
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        CallableNodeTraverser $callableNodeTraverser
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    public function getFirstConcatItem(Concat $concat): Node
    {
        // go to the deep, until there is no concat
        while ($concat->left instanceof Concat) {
            $concat = $concat->left;
        }

        return $concat->left;
    }

    public function removeFirstItemFromConcat(Concat $concat): Node
    {
        // just 2 items, return right one
        if (! $concat->left instanceof Concat) {
            return $concat->right;
        }

        $newConcat = clone $concat;
        $firstConcatItem = $this->getFirstConcatItem($concat);

        $this->callableNodeTraverser->traverseNodesWithCallable($newConcat, function (Node $node) use (
            $firstConcatItem
        ): ?Expr {
            if (! $node instanceof Concat) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($node->left, $firstConcatItem)) {
                return null;
            }

            return $node->right;
        });

        return $newConcat;
    }
}
