<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;

final class NodeToRemoveAndConcatItem
{
    public function __construct(
        private readonly Expr $removedExpr,
        private readonly Node $concatItemNode
    ) {
    }

    public function getRemovedExpr(): Expr
    {
        return $this->removedExpr;
    }

    public function getConcatItemNode(): Node
    {
        return $this->concatItemNode;
    }
}
