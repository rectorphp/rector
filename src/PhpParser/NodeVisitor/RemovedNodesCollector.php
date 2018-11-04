<?php declare(strict_types=1);

namespace Rector\PhpParser\NodeVisitor;

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

    public function removeNode(Node $nodeToRemove): void
    {
        foreach ($this->nodes as $key => $node) {
            if ($node !== $nodeToRemove) {
                continue;
            }

            unset($this->nodes[$key]);
            return;
        }
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
