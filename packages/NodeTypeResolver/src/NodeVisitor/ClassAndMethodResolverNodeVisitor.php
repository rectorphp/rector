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
use Rector\NodeTypeResolver\Node\MetadataAttribute;

/**
 * @todo should be checked by CS for the "NodeVisitor" suffix
 * it's confusing otherwise
 */
final class ClassAndMethodResolverNodeVisitor extends NodeVisitorAbstract
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
    private $methodCallName;

    /**
     * @param Node[] $nodes
     */
    public function afterTraverse(array $nodes): void
    {
        $this->classNode = null;
        $this->className = null;
        $this->methodName = null;
        $this->methodNode = null;
        $this->methodCallName = null;
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Class_ && $node->isAnonymous()) {
            return;
        }

        $this->processClass($node);
        $this->processMethod($node);
    }

    public function leaveNode(Node $node): void
    {
        if ($node instanceof Expression) {
            $this->methodCallName = null;
        }
    }

    private function processClass(Node $node): void
    {
        if ($node instanceof ClassLike) {
            $this->classNode = $node;
            $this->className = $node->namespacedName->toString();
        }

        $node->setAttribute(MetadataAttribute::CLASS_NODE, $this->classNode);
        $node->setAttribute(MetadataAttribute::CLASS_NAME, $this->className);

        if ($this->classNode instanceof Class_) {
            $this->setParentClassName($this->classNode, $node);
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

        $node->setAttribute(MetadataAttribute::PARENT_CLASS_NAME, $parentClassResolvedName);
    }

    private function processMethod(Node $node): void
    {
        if ($node instanceof ClassMethod) {
            $this->methodNode = $node;
            $this->methodName = (string) $node->name;
        }

        if ($node instanceof MethodCall && $node->name instanceof Identifier) {
            $this->methodCallName = $node->name->toString();
        }

        $node->setAttribute(MetadataAttribute::METHOD_NAME, $this->methodName);
        $node->setAttribute(MetadataAttribute::METHOD_NODE, $this->methodNode);
        $node->setAttribute(MetadataAttribute::METHOD_CALL_NAME, $this->methodCallName);
    }
}
