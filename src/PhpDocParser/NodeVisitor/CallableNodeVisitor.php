<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
final class CallableNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var callable(Node): (int|Node|null|Node[])
     */
    private $callable;
    private ?int $nodeIdToRemove = null;
    /**
     * @var array<int, Node[]>
     */
    private array $nodesToReturn = [];
    /**
     * @param callable(Node $node): (int|Node|null|Node[]) $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }
    /**
     * @return int|\PhpParser\Node|null
     */
    public function enterNode(Node $node)
    {
        $originalNode = $node;
        $callable = $this->callable;
        /** @var int|Node|null|Node[] $newNode */
        $newNode = $callable($node);
        if ($newNode === NodeVisitor::REMOVE_NODE) {
            $this->nodeIdToRemove = \spl_object_id($originalNode);
            return $originalNode;
        }
        if (\is_array($newNode)) {
            $nodeId = \spl_object_id($node);
            $this->nodesToReturn[$nodeId] = $newNode;
            return $node;
        }
        if ($originalNode instanceof Stmt && $newNode instanceof Expr) {
            return new Expression($newNode);
        }
        return $newNode;
    }
    /**
     * @return int|Node|Node[]
     */
    public function leaveNode(Node $node)
    {
        if ($this->nodeIdToRemove !== null && $this->nodeIdToRemove === \spl_object_id($node)) {
            $this->nodeIdToRemove = null;
            return NodeVisitor::REMOVE_NODE;
        }
        if ($this->nodesToReturn === []) {
            return $node;
        }
        return $this->nodesToReturn[\spl_object_id($node)] ?? $node;
    }
}
