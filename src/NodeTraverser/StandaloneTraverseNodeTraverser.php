<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

/**
 * This traverser traverse all nodes by one NodeVisitor,
 * then passed to another NodeVisitor.
 */
final class StandaloneTraverseNodeTraverser
{
    /**
     * @var NodeTraverser
     */
    private $nativeNodeTraverser;

    /**
     * @var NodeVisitor[]
     */
    private $nodeVisitors = [];

    public function __construct(NodeTraverser $nativeNodeTraverser)
    {
        $this->nativeNodeTraverser = $nativeNodeTraverser;
    }

    public function addNodeVisitor(NodeVisitor $nodeVisitor): void
    {
        $this->nodeVisitors[] = $nodeVisitor;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        foreach ($this->nodeVisitors as $nodeVisitor) {
            $this->nativeNodeTraverser->addVisitor($nodeVisitor);
            $nodes = $this->nativeNodeTraverser->traverse($nodes);
            $this->nativeNodeTraverser->removeVisitor($nodeVisitor);
        }

        return $nodes;
    }
}
