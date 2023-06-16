<?php

declare (strict_types=1);
namespace Rector\ReadWrite\ParentNodeReadAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\Contract\ParentNodeReadAnalyzerInterface;
final class ArrayDimFetchParentNodeReadAnalyzer implements ParentNodeReadAnalyzerInterface
{
    public function isRead(Expr $expr, Node $parentNode) : bool
    {
        if (!$parentNode instanceof ArrayDimFetch) {
            return \false;
        }
        if ($parentNode->dim !== $expr) {
            return \false;
        }
        // is left part of assign
        return $expr->getAttribute(AttributeKey::IS_ASSIGNED_TO) === \true;
    }
}
