<?php declare(strict_types=1);

namespace Rector\NodeVisitor;

use PhpParser\Node;

final class RemovedNodesCollector
{
    /**
     * @var Node[]
     */
    private $nodes = [];

    public function addNode(Node $node): void
    {
        $this->nodes[] = $node;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getNodesCount(): int
    {
        return count($this->nodes);
    }

    public function hasNode(Node $node): bool
    {
        return in_array($node, $this->nodes, true);
    }
}
