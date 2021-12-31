<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Symplify\Astral\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
final class CallableNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @var callable
     */
    private $callable;
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }
    /**
     * @return int|Node|null
     */
    public function enterNode(\PhpParser\Node $node)
    {
        $originalNode = $node;
        $callable = $this->callable;
        /** @var int|Node|null $newNode */
        $newNode = $callable($node);
        if ($originalNode instanceof \PhpParser\Node\Stmt && $newNode instanceof \PhpParser\Node\Expr) {
            return new \PhpParser\Node\Stmt\Expression($newNode);
        }
        return $newNode;
    }
}
