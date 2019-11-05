<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
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
     * @var ClassLike[]|null[]
     */
    private $classStack = [];

    /**
     * @var ClassMethod[]|null[]
     */
    private $methodStack = [];

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
        $this->functionNode = null;

        return null;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        $this->processClass($node);
        $this->processMethod($node);
        $this->processFunction($node);

        return $node;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassLike) {
            $this->setClassNodeAndName(array_pop($this->classStack));
        }
        if ($node instanceof ClassMethod) {
            $this->methodNode = array_pop($this->methodStack);
            $this->methodName = (string) $this->methodName;
        }
        return null;
    }

    private function processClass(Node $node): void
    {
        if ($node instanceof ClassLike) {
            $this->classStack[] = $this->classNode;
            $this->setClassNodeAndName($node);
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
            $this->methodStack[] = $this->methodNode;

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
        }

        $node->setAttribute(AttributeKey::FUNCTION_NODE, $this->functionNode);
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

    private function setClassNodeAndName(?ClassLike $classLike): void
    {
        $this->classNode = $classLike;
        if ($classLike === null || $classLike->name === null) {
            $this->className = null;
        } elseif (isset($classLike->namespacedName)) {
            $this->className = $classLike->namespacedName->toString();
        } else {
            $this->className = (string) $classLike->name;
        }
    }
}
