<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;

abstract class AbstractChangeParentClassRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        return $this->getParentClassName($node) === $this->getOldClassName();
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->extends = new Name('\\' . $this->getNewClassName());

        return $node;
    }

    abstract protected function getOldClassName(): string;

    abstract protected function getNewClassName(): string;

    private function getParentClassName(Class_ $classNode): string
    {
        if (! $classNode->extends) {
            return '';
        }

        /** @var Name $parentClassName */
        $parentClassNameNode = $classNode->extends;

        /** @var Node\Name\FullyQualified $fsqName */
        $fsqName = $parentClassNameNode->getAttribute('resolvedName');

        return $fsqName->toString();
    }
}
