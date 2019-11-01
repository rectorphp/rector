<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node\Stmt\Function_;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FunctionMethodAndClassNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string|null
     */
    private $methodName;

    /**
     * @var string|null
     */
    private $className;

    /**
     * @var string|null
     */
    private $functionName;

    /**
     * @var ClassLike|null
     */
    private $classNode;

    /**
     * @var ClassMethod|null
     */
    private $methodNode;

    /**
     * @var Node\Stmt\Function_|null
     */
    private $functionNode;

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->classNode = null;
        $this->className = null;
        $this->methodName = null;
        $this->methodNode = null;
        $this->functionName = null;
        $this->functionNode = null;

        return null;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($this->isClassAnonymous($node)) {
            return null;
        }

        // skip anonymous classes
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Class_ && $this->isClassAnonymous($parentNode)) {
            return null;
        }

        $this->processClass($node);
        $this->processMethod($node);
        $this->processFunction($node);

        return $node;
    }

    private function isClassAnonymous(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        if ($node->isAnonymous()) {
            return true;
        }

        if ($node->name === null) {
            return true;
        }

        // PHPStan polution
        return (bool) Strings::match($node->name->toString(), '#^AnonymousClass\w+#');
    }

    private function processClass(Node $node): void
    {
        if ($node instanceof ClassLike) {
            $this->classNode = $node;
            $this->className = $node->namespacedName->toString();
        }

        $node->setAttribute(AttributeKey::CLASS_NODE, $this->classNode);
        $node->setAttribute(AttributeKey::CLASS_NAME, $this->className);

        if ($this->classNode instanceof Class_) {
            $this->setParentClassName($this->classNode, $node);
        }
    }

    private function processMethod(Node $node): void
    {
        if ($node instanceof ClassMethod) {
            $this->methodNode = $node;
            $this->methodName = (string) $node->name;
        }

        $node->setAttribute(AttributeKey::METHOD_NAME, $this->methodName);
        $node->setAttribute(AttributeKey::METHOD_NODE, $this->methodNode);
    }

    private function processFunction(Node $node): void
    {
        if ($node instanceof Function_) {
            $this->functionNode = $node;
            $this->functionName = (string) $node->name;
        }

        $node->setAttribute(AttributeKey::FUNCTION_NODE, $this->functionNode);
        $node->setAttribute(AttributeKey::FUNCTION_NAME, $this->functionName);
    }

    private function setParentClassName(Class_ $classNode, Node $node): void
    {
        if ($classNode->extends === null) {
            return;
        }

        $parentClassResolvedName = $classNode->extends->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($parentClassResolvedName instanceof FullyQualified) {
            $parentClassResolvedName = $parentClassResolvedName->toString();
        }

        $node->setAttribute(AttributeKey::PARENT_CLASS_NAME, $parentClassResolvedName);
    }
}
