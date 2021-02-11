<?php

declare(strict_types=1);

namespace Rector\ReadWrite\ReadNodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ReadWrite\NodeFinder\NodeUsageFinder;

abstract class AbstractReadNodeAnalyzer
{
    /**
     * @var ParentScopeFinder
     */
    protected $parentScopeFinder;

    /**
     * @var NodeUsageFinder
     */
    protected $nodeUsageFinder;

    /**
     * @required
     */
    public function autowireAbstractReadNodeAnalyzer(
        ParentScopeFinder $parentScopeFinder,
        NodeUsageFinder $nodeUsageFinder
    ): void {
        $this->parentScopeFinder = $parentScopeFinder;
        $this->nodeUsageFinder = $nodeUsageFinder;
    }

    protected function isCurrentContextRead(Expr $expr): bool
    {
        $parent = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Return_) {
            return true;
        }

        if ($parent instanceof Arg) {
            return true;
        }

        if ($parent instanceof ArrayDimFetch) {
            $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parentParent instanceof Assign) {
                return true;
            }

            return $parentParent->var !== $parent;
        }

        // assume it's used by default
        return ! $parent instanceof Expression;
    }
}
