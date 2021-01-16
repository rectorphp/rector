<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ConcatManipulator
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
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

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($newConcat, function (Node $node) use (
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
