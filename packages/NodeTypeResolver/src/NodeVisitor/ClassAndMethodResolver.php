<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

/**
 * Adds attribute to all nodes inside:
 * - 'className' with current class name
 * - 'classNode' with current class node
 * - 'parentClassName' with current class node
 * - 'methodName' with current method name
 * - 'methodNode' with current method node
 * - 'methodCall' with current method call
 */
final class ClassAndMethodResolver extends NodeVisitorAbstract
{
    /**
     * @var ClassLike|null
     */
    private $classNode;

    /**
     * @var string|null
     */
    private $className;

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
        $this->classNode = null;
        $this->className = null;
        $this->methodName = null;
        $this->methodNode = null;
        $this->methodCall = null;
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Class_ && $node->isAnonymous()) {
            return;
        }

        if ($node instanceof ClassLike) {
            $this->classNode = $node;
            $this->className = $node->namespacedName->toString();
        }

        $node->setAttribute(Attribute::CLASS_NODE, $this->classNode);
        $node->setAttribute(Attribute::CLASS_NAME, $this->className);

        if ($this->classNode instanceof Class_) {
            $this->setParentClassName($this->classNode, $node);
        }

        if ($node instanceof ClassMethod) {
            $this->methodNode = $node;

            /** @var Identifier $identifierNode */
            $identifierNode = $node->name;

            $this->methodName = $identifierNode->toString();
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

    private function setParentClassName(Class_ $classNode, Node $node): void
    {
        if ($classNode->extends === null) {
            return;
        }

        $parentClassResolvedName = $classNode->extends->getAttribute(Attribute::RESOLVED_NAME);

        if ($parentClassResolvedName instanceof FullyQualified) {
            $parentClassResolvedName = $parentClassResolvedName->toString();
        }

        $node->setAttribute(Attribute::PARENT_CLASS_NAME, $parentClassResolvedName);
    }
}
