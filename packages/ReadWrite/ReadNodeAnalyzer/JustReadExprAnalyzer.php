<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\ReadWrite\ReadNodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class JustReadExprAnalyzer
{
    public function isReadContext(Expr $expr) : bool
    {
        $parent = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Return_) {
            return \true;
        }
        if ($parent instanceof Arg) {
            return \true;
        }
        if ($parent instanceof ArrayDimFetch) {
            $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parentParent instanceof Assign) {
                return \true;
            }
            return $parentParent->var !== $parent;
        }
        // assume it's used by default
        return !$parent instanceof Expression;
    }
}
