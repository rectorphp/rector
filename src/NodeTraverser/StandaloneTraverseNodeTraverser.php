<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverserInterface;
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

    /**
     * @var NodeTraverserInterface[]
     */
    private $nodeTraversers = [];

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
        foreach ($this->getNodeTraversers() as $nodeTraverser) {
            $nodes = $nodeTraverser->traverse($nodes);
        }

        return $nodes;
    }

    /**
     * @return NodeTraverserInterface[]
     */
    private function getNodeTraversers(): array
    {
        if ($this->nodeTraversers) {
            return $this->nodeTraversers;
        }

        foreach ($this->nodeVisitors as $nodeVisitor) {
            $this->nodeTraversers[] = $this->nodeTraverserFactory->createWithNodeVisitor($nodeVisitor);
        }

        return $this->nodeTraversers;
    }
}
