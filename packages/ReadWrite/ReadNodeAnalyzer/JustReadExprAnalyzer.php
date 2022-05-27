<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class JustReadExprAnalyzer
{
    public function isReadContext(\PhpParser\Node\Expr $expr) : bool
    {
        $parent = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\Return_) {
            return \true;
        }
        if ($parent instanceof \PhpParser\Node\Arg) {
            return \true;
        }
        if ($parent instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $parentParent = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parentParent instanceof \PhpParser\Node\Expr\Assign) {
                return \true;
            }
            return $parentParent->var !== $parent;
        }
        // assume it's used by default
        return !$parent instanceof \PhpParser\Node\Stmt\Expression;
    }
}
