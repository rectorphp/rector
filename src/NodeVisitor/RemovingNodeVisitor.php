<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class RemovingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var RemovedNodesCollector
     */
    private $removedNodesCollector;

    public function __construct(RemovedNodesCollector $removedNodesCollector)
    {
        $this->removedNodesCollector = $removedNodesCollector;
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        if (! $this->removedNodesCollector->getNodesCount()) {
            return;
        }
    }

    /**
     * @return int|Node|Node[]|null
     */
    public function leaveNode(Node $node)
    {
        if ($this->removedNodesCollector->hasNode($node)) {
            return NodeTraverser::REMOVE_NODE;
        }

        return $node;
    }
}
