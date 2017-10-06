<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;

final class ClassReplacerRector extends AbstractRector
{

    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @param string[] $oldToNewClasses
     */
    public function __construct(array $oldToNewClasses)
    {
        $this->oldToNewClasses = $oldToNewClasses;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Name) {
            return false;
        }

        $fqnName = $node->toString();

        return isset($this->oldToNewClasses[$fqnName]);
    }

    /**
     * @param Name $nameNode
     */
    public function refactor(Node $nameNode): ?Node
    {
        $newName = $this->getNewName($nameNode->toString());

        // if already present use statement, just null it
        // ... neturn Nop()

        return new FullyQualified($newName);
    }

    private function getNewName(string $oldName): string
    {
        return $this->oldToNewClasses[$oldName];
    }
}
