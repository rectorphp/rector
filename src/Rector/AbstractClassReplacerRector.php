<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;

abstract class AbstractClassReplacerRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Name) {
            return false;
        }

        $fqnName = $node->toString();

        return isset($this->getOldToNewClasses()[$fqnName]);
    }

    /**
     * @param Name $node
     */
    public function refactor(Node $node): ?Node
    {
        $newName = $this->getNewName($node->toString());

        return new FullyQualified($newName);
    }

    /**
     * @return string[]
     */
    abstract protected function getOldToNewClasses(): array;

    private function getNewName(string $oldName): string
    {
        return $this->getOldToNewClasses()[$oldName];
    }
}
