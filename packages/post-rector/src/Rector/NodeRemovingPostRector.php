<?php

declare(strict_types=1);

namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\PostRector\Contract\Rector\PostRectorInterface;

final class NodeRemovingPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        NodesToRemoveCollector $nodesToRemoveCollector
    ) {
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getPriority(): int
    {
        return 800;
    }

    public function enterNode(Node $node): ?Node
    {
        if (! $this->nodesToRemoveCollector->isActive()) {
            return null;
        }

        // special case for fluent methods
        foreach ($this->nodesToRemoveCollector->getNodesToRemove() as $key => $nodeToRemove) {
            if (! $node instanceof MethodCall) {
                continue;
            }

            if (! $nodeToRemove instanceof MethodCall) {
                continue;
            }

            // replace chain method call by non-chain method call
            if (! $this->isChainMethodCallNodeToBeRemoved($node, $nodeToRemove)) {
                continue;
            }

            $this->nodesToRemoveCollector->unset($key);

            $methodName = $this->nodeNameResolver->getName($node->name);

            /** @var MethodCall $nestedMethodCall */
            $nestedMethodCall = $node->var;

            /** @var string $methodName */
            return $this->nodeFactory->createMethodCall($nestedMethodCall->var, $methodName, $node->args);
        }

        if (! $node instanceof BinaryOp) {
            return null;
        }

        return $this->removePartOfBinaryOp($node);
    }

    /**
     * @return int|Node
     */
    public function leaveNode(Node $node)
    {
        foreach ($this->nodesToRemoveCollector->getNodesToRemove() as $key => $nodeToRemove) {
            $nodeToRemoveParent = $nodeToRemove->getAttribute(AttributeKey::PARENT_NODE);
            if ($nodeToRemoveParent instanceof BinaryOp) {
                continue;
            }

            if ($node === $nodeToRemove) {
                $this->nodesToRemoveCollector->unset($key);

                return NodeTraverser::REMOVE_NODE;
            }
        }

        return $node;
    }

    private function isChainMethodCallNodeToBeRemoved(
        MethodCall $mainMethodCall,
        MethodCall $toBeRemovedMethodCall
    ): bool {
        if (! $mainMethodCall->var instanceof MethodCall) {
            return false;
        }
        if ($toBeRemovedMethodCall !== $mainMethodCall->var) {
            return false;
        }

        $methodName = $this->nodeNameResolver->getName($mainMethodCall->name);

        return $methodName !== null;
    }

    private function removePartOfBinaryOp(BinaryOp $binaryOp): ?Node
    {
        // handle left/right binary remove, e.g. "true && false" → remove false → "true"
        foreach ($this->nodesToRemoveCollector->getNodesToRemove() as $key => $nodeToRemove) {
            // remove node
            $nodeToRemoveParentNode = $nodeToRemove->getAttribute(AttributeKey::PARENT_NODE);
            if (! $nodeToRemoveParentNode instanceof BinaryOp) {
                continue;
            }

            if ($binaryOp->left === $nodeToRemove) {
                $this->nodesToRemoveCollector->unset($key);
                return $binaryOp->right;
            }

            if ($binaryOp->right === $nodeToRemove) {
                $this->nodesToRemoveCollector->unset($key);
                return $binaryOp->left;
            }
        }

        return null;
    }
}
