<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ParentNodeReadAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
final class ArrayDimFetchParentNodeReadAnalyzer implements \Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isRead(\PhpParser\Node\Expr $expr, \PhpParser\Node $parentNode) : bool
    {
        if (!$parentNode instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return \false;
        }
        if ($parentNode->dim !== $expr) {
            return \false;
        }
        // is left part of assign
        return $this->isLeftPartOfAssign($expr);
    }
    private function isLeftPartOfAssign(\PhpParser\Node\Expr $expr) : bool
    {
        $parentAssign = $this->betterNodeFinder->findParentType($expr, \PhpParser\Node\Expr\Assign::class);
        if (!$parentAssign instanceof \PhpParser\Node\Expr\Assign) {
            return \true;
        }
        return !(bool) $this->betterNodeFinder->findFirst($parentAssign->var, function (\PhpParser\Node $node) use($expr) : bool {
            return $node === $expr;
        });
    }
}
