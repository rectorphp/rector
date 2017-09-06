<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;

abstract class AbstractClassAwareRector extends AbstractRector
{
    /**
     * @var Class_|null
     */
    protected $classNode;

    /**
     * @param Node[] $nodes
     * @return null|Node[]
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->classNode = null;

        foreach ($nodes as $node) {
            if ($node instanceof Class_) {
                $this->classNode = $node;
                break;
            }
        }

        return null;
    }

    protected function getClassName(): string
    {
        if ($this->classNode === null) {
            return '';
        }

        return $this->classNode->namespacedName->toString();
    }

    protected function getParentClassName(): string
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
