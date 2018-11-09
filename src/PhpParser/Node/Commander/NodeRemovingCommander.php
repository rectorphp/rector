<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\NodeTypeResolver\Node\Attribute;

final class NodeRemovingCommander implements CommanderInterface
{
    /**
     * @var Stmt[]
     */
    private $nodesToRemove = [];

    public function addNode(Node $node): void
    {
        if (! $node instanceof Expression && ($node->getAttribute(Attribute::PARENT_NODE) instanceof Expression)) {
            // only expressions can be removed
            $node = $node->getAttribute(Attribute::PARENT_NODE);
        }

        /** @var Stmt $node */
        $this->nodesToRemove[] = $node;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->createNodeVisitor());

        // new nodes to remove are always per traverse
        $this->nodesToRemove = [];

        return $nodeTraverser->traverse($nodes);
    }

    private function createNodeVisitor(): NodeVisitor
    {
        return new class($this->nodesToRemove) extends NodeVisitorAbstract {
            /**
             * @var Stmt[]
             */
            private $nodesToRemove = [];

            /**
             * @param Stmt[] $nodesToRemove
             */
            public function __construct(array $nodesToRemove)
            {
                $this->nodesToRemove = $nodesToRemove;
            }

            /**
             * @return int|Node|Node[]|null
             */
            public function leaveNode(Node $node)
            {
                foreach ($this->nodesToRemove as $key => $nodeToRemove) {
                    if ($node === $nodeToRemove) {
                        unset($this->nodesToRemove[$key]);
                        return NodeTraverser::REMOVE_NODE;
                    }
                }

                return $node;
            }
        };
    }

    public function isActive(): bool
    {
        return count($this->nodesToRemove) > 0;
    }
}
