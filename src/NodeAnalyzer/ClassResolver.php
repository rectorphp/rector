<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

final class ClassResolver
{
    /**
     * @var Class_|null
     */
    private $classNode;

    /**
     * @param Node[] $nodes
     */
    public function resolveFromNodes(array $nodes): void
    {
        $this->classNode = null;

        foreach ($nodes as $node) {
            if ($node instanceof Class_) {
                $this->classNode = $node;
                break;
            }
        }
    }

    public function getClassName(): string
    {
        if ($this->classNode === null) {
            return '';
        }

        return $this->classNode->namespacedName->toString();
    }

    public function getParentClassName(): string
    {
        if ($this->classNode === null) {
            return '';
        }

        $parentClass = $this->classNode->extends;

        /** @var Node\Name\FullyQualified $fqnParentClassName */
        $fqnParentClassName = $parentClass->getAttribute('resolvedName');

        return $fqnParentClassName->toString();
    }
}
