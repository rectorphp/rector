<?php

declare(strict_types=1);

namespace Rector\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\Node\CommanderInterface;

/**
 * Adds new private properties to class + to constructor
 */
final class NodeReplacingCommander implements CommanderInterface
{
    /**
     * @var Node[][]
     */
    private $nodesToReplace = [];

    public function replaceNode(Node $node, Node $replaceWith): void
    {
        $this->nodesToReplace[] = [$node, $replaceWith];
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->createNodeVisitor());
        return $nodeTraverser->traverse($nodes);
    }

    public function isActive(): bool
    {
        return count($this->nodesToReplace) > 0;
    }

    public function getPriority(): int
    {
        return 1100;
    }

    private function createNodeVisitor(): NodeVisitor
    {
        return new class($this->nodesToReplace) extends NodeVisitorAbstract {
            /**
             * @var Node[][]
             */
            private $nodesToReplace = [];

            public function __construct(array $nodesToReplace)
            {
                $this->nodesToReplace = $nodesToReplace;
            }

            public function leaveNode(Node $node): ?Node
            {
                foreach ($this->nodesToReplace as [$nodeToFind, $replacement]) {
                    if ($node === $nodeToFind) {
                        return $replacement;
                    }
                }
                return null;
            }
        };
    }
}
