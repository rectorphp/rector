<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Commander;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\Node\CommanderInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class NodeRemovingCommander implements CommanderInterface
{
    /**
     * @var Stmt[]
     */
    private $nodesToRemove = [];

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NodeFactory $nodeFactory, NameResolver $nameResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nameResolver = $nameResolver;
    }

    public function addNode(Node $node): void
    {
        // chain call: "->method()->another()"
        if ($node instanceof MethodCall && $node->var instanceof MethodCall) {
            throw new ShouldNotHappenException(
                'Chain method calls cannot be removed this way. It would remove the whole tree of calls. Remove them manually by creating new parent node with no following method.'
            );
        }

        if (! $node instanceof Expression && ($node->getAttribute(
            AttributeKey::PARENT_NODE
        ) instanceof Expression)) {
            // only expressions can be removed
            $node = $node->getAttribute(AttributeKey::PARENT_NODE);
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

    public function isActive(): bool
    {
        return count($this->nodesToRemove) > 0;
    }

    public function isNodeRemoved(Node $node): bool
    {
        return in_array($node, $this->nodesToRemove, true);
    }

    private function createNodeVisitor(): NodeVisitor
    {
        return new class($this->nodesToRemove, $this->nodeFactory, $this->nameResolver) extends NodeVisitorAbstract {
            /**
             * @var Stmt[]|Expr[]
             */
            private $nodesToRemove = [];

            /**
             * @var NodeFactory
             */
            private $nodeFactory;

            /**
             * @var NameResolver
             */
            private $nameResolver;

            /**
             * @param Stmt[] $nodesToRemove
             */
            public function __construct(array $nodesToRemove, NodeFactory $nodeFactory, NameResolver $nameResolver)
            {
                $this->nodesToRemove = $nodesToRemove;
                $this->nodeFactory = $nodeFactory;
                $this->nameResolver = $nameResolver;
            }

            /**
             * @return int|Node|null
             */
            public function enterNode(Node $node)
            {
                // special case for fluent methods
                foreach ($this->nodesToRemove as $key => $nodeToRemove) {
                    if (! $nodeToRemove instanceof MethodCall) {
                        continue;
                    }

                    if (! $node instanceof MethodCall || ! $node->var instanceof MethodCall) {
                        continue;
                    }

                    if ($nodeToRemove !== $node->var) {
                        continue;
                    }

                    $methodName = $this->nameResolver->resolve($node->name);
                    if ($methodName === null) {
                        continue;
                    }

                    unset($this->nodesToRemove[$key]);

                    return $this->nodeFactory->createMethodCall($node->var->var, $methodName, $node->args);
                }

                return null;
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
}
