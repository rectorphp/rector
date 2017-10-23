<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

/**
 * Adds attribute to all nodes inside:
 * - 'methodName' with current method name
 * - 'methodNode' with current method node
 * - 'methodCall' with current method call
 */
final class MethodResolver extends NodeVisitorAbstract
{
    /**
     * @var string|null
     */
    private $methodName;

    /**
     * @var ClassMethod|null
     */
    private $methodNode;

    /**
     * @var string|null
     */
    private $methodCall;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->methodName = null;
        $this->methodCall = null;
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof ClassMethod) {
            $this->methodNode = $node;
            $this->methodName = $node->name->toString();
        }

        if ($node instanceof MethodCall && $node->name instanceof Identifier) {
            $this->methodCall = $node->name->toString();
        }

        $node->setAttribute(Attribute::METHOD_NAME, $this->methodName);
        $node->setAttribute(Attribute::METHOD_NODE, $this->methodNode);
        $node->setAttribute(Attribute::METHOD_CALL, $this->methodCall);
    }

    public function leaveNode(Node $node): void
    {
        if ($node instanceof Expression) {
            $this->methodCall = null;
        }
    }
}
