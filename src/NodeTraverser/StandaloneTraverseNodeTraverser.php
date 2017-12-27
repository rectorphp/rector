<?php declare(strict_types=1);

namespace Rector\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;

/**
 * Oppose to NodeTraverser, that traverse ONE node by ALL NodeVisitors,
 * this traverser traverse ALL nodes by one NodeVisitor, THEN passes them to next NodeVisitor.
 */
final class StandaloneTraverseNodeTraverser
{
    /**
     * @var NodeTraverserInterface[]
     */
    private $nodeTraversers = [];

    public function addNodeVisitor(NodeVisitor $nodeVisitor): void
    {
        $this->addNodeVisitors([$nodeVisitor]);
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
