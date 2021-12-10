<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class NodeRemovingPostRector extends \Rector\PostRector\Rector\AbstractPostRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\PostRector\Collector\NodesToRemoveCollector $nodesToRemoveCollector)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }
    public function getPriority() : int
    {
        return 800;
    }
    public function enterNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodesToRemoveCollector->isActive()) {
            return null;
        }
        // special case for fluent methods
        foreach ($this->nodesToRemoveCollector->getNodesToRemove() as $key => $nodeToRemove) {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                continue;
            }
            if (!$nodeToRemove instanceof \PhpParser\Node\Expr\MethodCall) {
                continue;
            }
            // replace chain method call by non-chain method call
            if (!$this->isChainMethodCallNodeToBeRemoved($node, $nodeToRemove)) {
                continue;
            }
            $this->nodesToRemoveCollector->unset($key);
            $methodName = $this->nodeNameResolver->getName($node->name);
            /** @var MethodCall $nestedMethodCall */
            $nestedMethodCall = $node->var;
            /** @var string $methodName */
            return $this->nodeFactory->createMethodCall($nestedMethodCall->var, $methodName, $node->args);
        }
        if (!$node instanceof \PhpParser\Node\Expr\BinaryOp) {
            return null;
        }
        return $this->removePartOfBinaryOp($node);
    }
    /**
     * @return int|\PhpParser\Node
     */
    public function leaveNode(\PhpParser\Node $node)
    {
        foreach ($this->nodesToRemoveCollector->getNodesToRemove() as $key => $nodeToRemove) {
            $nodeToRemoveParent = $nodeToRemove->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($nodeToRemoveParent instanceof \PhpParser\Node\Expr\BinaryOp) {
                continue;
            }
            if ($node === $nodeToRemove) {
                $this->nodesToRemoveCollector->unset($key);
                return \PhpParser\NodeTraverser::REMOVE_NODE;
            }
        }
        return $node;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove nodes from weird positions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return 1;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return 1;
    }
}
CODE_SAMPLE
)]);
    }
    private function isChainMethodCallNodeToBeRemoved(\PhpParser\Node\Expr\MethodCall $mainMethodCall, \PhpParser\Node\Expr\MethodCall $toBeRemovedMethodCall) : bool
    {
        if (!$mainMethodCall->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if ($toBeRemovedMethodCall !== $mainMethodCall->var) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($mainMethodCall->name);
        return $methodName !== null;
    }
    private function removePartOfBinaryOp(\PhpParser\Node\Expr\BinaryOp $binaryOp) : ?\PhpParser\Node
    {
        // handle left/right binary remove, e.g. "true && false" → remove false → "true"
        foreach ($this->nodesToRemoveCollector->getNodesToRemove() as $key => $nodeToRemove) {
            // remove node
            $nodeToRemoveParentNode = $nodeToRemove->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$nodeToRemoveParentNode instanceof \PhpParser\Node\Expr\BinaryOp) {
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
