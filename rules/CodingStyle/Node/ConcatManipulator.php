<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Node;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ConcatManipulator
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeComparator = $nodeComparator;
    }
    public function getFirstConcatItem(\PhpParser\Node\Expr\BinaryOp\Concat $concat) : \PhpParser\Node\Expr
    {
        // go to the deep, until there is no concat
        while ($concat->left instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            $concat = $concat->left;
        }
        return $concat->left;
    }
    public function removeFirstItemFromConcat(\PhpParser\Node\Expr\BinaryOp\Concat $concat) : \PhpParser\Node\Expr
    {
        // just 2 items, return right one
        if (!$concat->left instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            return $concat->right;
        }
        $newConcat = clone $concat;
        $firstConcatItem = $this->getFirstConcatItem($concat);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($newConcat, function (\PhpParser\Node $node) use($firstConcatItem) : ?Expr {
            if (!$node instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($node->left, $firstConcatItem)) {
                return null;
            }
            return $node->right;
        });
        return $newConcat;
    }
}
