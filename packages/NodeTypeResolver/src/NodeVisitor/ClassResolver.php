<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

/**
 * Add attribute 'class' with current class name.
 * Add 'use_imports' with all related uses.
 */
final class ClassResolver extends NodeVisitorAbstract
{
    /**
     * @var ClassLike|null
     */
    private $classNode;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->classNode = null;
    }

    public function enterNode(Node $node): void
    {
        // detect only first ClassLike element
        if ($this->classNode === null && $node instanceof ClassLike) {
            // skip possible anonymous classes
            if ($node instanceof Class_ && $node->isAnonymous()) {
                return;
            }

            $this->classNode = $node;
        }

        if ($this->classNode === null) {
            return;
        }

        $node->setAttribute(Attribute::CLASS_NODE, $this->classNode);
        $node->setAttribute(Attribute::CLASS_NAME, $this->classNode->namespacedName->toString());

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

        $node->setAttribute(Attribute::PARENT_CLASS_NAME, $parentClassResolvedName);
    }
}
