<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;

final class NodeRemovingNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Stmt[]|Expr[]
     */
    private $nodesToRemove = [];

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @param Stmt[] $nodesToRemove
     */
    public function __construct(array $nodesToRemove, NodeFactory $nodeFactory, NodeNameResolver $nodeNameResolver)
    {
        $this->nodesToRemove = $nodesToRemove;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
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

            $methodName = $this->nodeNameResolver->getName($node->name);
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
}
