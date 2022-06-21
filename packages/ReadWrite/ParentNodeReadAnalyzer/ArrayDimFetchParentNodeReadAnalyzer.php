<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ParentNodeReadAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
final class ArrayDimFetchParentNodeReadAnalyzer implements ParentNodeReadAnalyzerInterface
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isRead(Expr $expr, Node $parentNode) : bool
    {
        if (!$parentNode instanceof ArrayDimFetch) {
            return \false;
        }
        if ($parentNode->dim !== $expr) {
            return \false;
        }
        // is left part of assign
        return $this->isLeftPartOfAssign($expr);
    }
    private function isLeftPartOfAssign(Expr $expr) : bool
    {
        $parentAssign = $this->betterNodeFinder->findParentType($expr, Assign::class);
        if (!$parentAssign instanceof Assign) {
            return \true;
        }
        return !(bool) $this->betterNodeFinder->findFirst($parentAssign->var, static function (Node $node) use($expr) : bool {
            return $node === $expr;
        });
    }
}
