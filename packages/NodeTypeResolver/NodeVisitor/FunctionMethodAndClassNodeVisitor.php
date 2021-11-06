<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class FunctionMethodAndClassNodeVisitor extends NodeVisitorAbstract
{
    private ?string $className = null;

    /**
     * @var ClassLike[]|null[]
     */
    private array $classStack = [];

    private ?ClassLike $classLike = null;

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->classLike = null;
        $this->className = null;

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        $this->processClass($node);

        return $node;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassLike) {
            $classLike = array_pop($this->classStack);
            $this->setClassNodeAndName($classLike);
        }

        return null;
    }

    private function processClass(Node $node): void
    {
        if ($node instanceof ClassLike) {
            $this->classStack[] = $this->classLike;
            $this->setClassNodeAndName($node);
        }

        $node->setAttribute(AttributeKey::CLASS_NAME, $this->className);
    }

    private function setClassNodeAndName(?ClassLike $classLike): void
    {
        $this->classLike = $classLike;
        if (! $classLike instanceof ClassLike || $classLike->name === null) {
            $this->className = null;
        } elseif (property_exists($classLike, 'namespacedName')) {
            $this->className = $classLike->namespacedName->toString();
        } else {
            $this->className = (string) $classLike->name;
        }
    }
}
