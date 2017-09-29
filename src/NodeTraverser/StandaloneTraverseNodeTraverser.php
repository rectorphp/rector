<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeVisitor;

/**
 * This traverser traverse all nodes by one NodeVisitor,
 * then passed to another NodeVisitor.
 */
final class StandaloneTraverseNodeTraverser
{
    /**
     * @var NodeVisitor[]
     */
    private $nodeVisitors = [];

    /**
     * @var NodeTraverserFactory
     */
    private $nodeTraverserFactory;

    public function __construct(NodeTraverserFactory $nodeTraverserFactory)
    {
        $this->nodeTraverserFactory = $nodeTraverserFactory;
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
            $nodeTraverser = $this->nodeTraverserFactory->createWithNoeVisitor($nodeVisitor);
            $nodes = $nodeTraverser->traverse($nodes);
        }

        return $nodes;
    }
}
