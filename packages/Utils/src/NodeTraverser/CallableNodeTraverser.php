<?php declare(strict_types=1);

namespace Rector\Utils\NodeTraverser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
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
        $nodeTraverser->addVisitor(new class($callable) extends NodeVisitorAbstract {
            /**
             * @var callable
             */
            private $callable;

            public function __construct(callable $callable)
            {
                $this->callable = $callable;
            }

            public function enterNode(Node $node): ?Node
            {
                $callable = $this->callable;
                return $callable($node);
            }
        });

        return $nodeTraverser->traverse($nodes);
    }
}
