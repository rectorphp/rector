<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassAndMethodNodeVisitor extends NodeVisitorAbstract
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
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->classNode = null;
        $this->className = null;
        $this->methodName = null;
        $this->methodNode = null;

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

        return $node;
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
}
