<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;

abstract class AbstractChangeParentClassRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_ || $node->extends === null) {
            return false;
        }

        /** @var FullyQualified $fqnName */
        $fqnName = $node->extends->getAttribute('resolvedName');

        return $fqnName->toString() === $this->getOldClassName();
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->extends = new FullyQualified($this->getNewClassName());

        return $node;
    }

    abstract protected function getOldClassName(): string;

    abstract protected function getNewClassName(): string;
}
