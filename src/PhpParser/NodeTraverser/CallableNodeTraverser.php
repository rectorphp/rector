<?php declare(strict_types=1);

namespace Rector\PhpParser\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

final class CallableNodeTraverser
{
    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodesWithCallable(array $nodes, callable $callable): array
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->createNodeVisitor($callable));

        return $nodeTraverser->traverse($nodes);
    }

    private function createNodeVisitor(callable $callable): NodeVisitor
    {
        return new class($callable) extends NodeVisitorAbstract {
            /**
             * @var callable
             */
            private $callable;

            public function __construct(callable $callable)
            {
                $this->callable = $callable;
            }

            /**
             * @return int|Node|null
             */
            public function enterNode(Node $node)
            {
                $callable = $this->callable;
                return $callable($node);
            }
        };
    }
}
