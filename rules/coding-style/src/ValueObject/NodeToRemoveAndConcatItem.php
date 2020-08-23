<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;

final class NodeToRemoveAndConcatItem
{
    /**
     * @var Expr
     */
    private $removedExpr;

    /**
     * @var Node
     */
    private $concatItemNode;

    public function __construct(Expr $removedExpr, Node $concatItemNode)
    {
        $this->removedExpr = $removedExpr;
        $this->concatItemNode = $concatItemNode;
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
