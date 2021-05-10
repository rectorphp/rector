<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;
final class NodeToRemoveAndConcatItem
{
    /**
     * @var \PhpParser\Node\Expr
     */
    private $removedExpr;
    /**
     * @var \PhpParser\Node
     */
    private $concatItemNode;
    public function __construct(\PhpParser\Node\Expr $removedExpr, \PhpParser\Node $concatItemNode)
    {
        $this->removedExpr = $removedExpr;
        $this->concatItemNode = $concatItemNode;
    }
    public function getRemovedExpr() : \PhpParser\Node\Expr
    {
        return $this->removedExpr;
    }
    public function getConcatItemNode() : \PhpParser\Node
    {
        return $this->concatItemNode;
    }
}
