<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Resolver\NameResolver;

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

            $methodName = $this->nameResolver->getName($node->name);
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
