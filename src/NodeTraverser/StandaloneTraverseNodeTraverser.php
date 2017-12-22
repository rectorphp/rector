<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;

/**
 * This traverser traverse all nodes by one NodeVisitor,
 * then passed to another NodeVisitor.
 */
final class StandaloneTraverseNodeTraverser
{
    /**
     * @var NodeTraverserInterface[]
     */
    private $nodeTraversers = [];

    public function addNodeVisitor(NodeVisitor $nodeVisitor): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($nodeVisitor);

        $this->nodeTraversers[] = $nodeTraverser;
    }

    /**
     * @param NodeVisitor[] $nodeVisitors
     */
    public function addNodeVisitors(array $nodeVisitors): void
    {
        $nodeTraverser = new NodeTraverser();
        foreach ($nodeVisitors as $nodeVisitor) {
            $nodeTraverser->addVisitor($nodeVisitor);
        }

        $this->nodeTraversers[] = $nodeTraverser;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        foreach ($this->nodeTraversers as $nodeTraverser) {
            $nodes = $nodeTraverser->traverse($nodes);
        }

        return $nodes;
    }
}
