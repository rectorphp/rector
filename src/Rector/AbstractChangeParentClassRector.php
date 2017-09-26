<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Node\Attribute;

abstract class AbstractChangeParentClassRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Class_ || $node->extends === null || $node->isAnonymous()) {
            return false;
        }

        /** @var FullyQualified $fqnName */
        $fqnName = $node->extends->getAttribute(Attribute::RESOLVED_NAME);

        if ($fqnName instanceof FullyQualified) {
            return $fqnName->toString() === $this->getOldClassName();
        }

        if ($node->extends instanceof FullyQualified) {
            return $node->extends->toString() === $this->getOldClassName();
        }
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
