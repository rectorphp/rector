<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Rector\AbstractRector;

final class NamespaceReplacerRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewNamespaces = [];

    /**
     * @param string[] $oldToNewNamespaces
     */
    public function __construct(array $oldToNewNamespaces)
    {
        $this->oldToNewNamespaces = $oldToNewNamespaces;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Namespace_ && ! $node instanceof Use_) {
            return false;
        }

        $name = $this->resolveNameFromNode($node);

        return $this->isNamespaceToChange($name);
    }

    /**
     * @param Namespace_|Use_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Namespace_) {
            $newName = $this->resolveNewNameFromNode($node);
            $node->name = new Name($newName);

            return $node;
        }

        return null;
    }

    private function resolveNewNameFromNode(Node $node): string
    {
        $name = $this->resolveNameFromNode($node);

        [$oldNamespace, $newNamespace] = $this->getNewNamespaceForOldOne($name);

        return str_replace($oldNamespace, $newNamespace, $name);
    }

    /**
     * @param Namespace_|Use_ $node
     */
    private function resolveNameFromNode(Node $node): string
    {
        if ($node instanceof Namespace_) {
            return $node->name->toString();
        }

        if ($node instanceof Use_) {
            return $node->uses[0]->name->toString();
        }
    }

    private function isNamespaceToChange(string $namespace): bool
    {
        foreach ($this->oldToNewNamespaces as $oldNamespace => $newNamespace) {
            if (Strings::startsWith($namespace, $oldNamespace)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getNewNamespaceForOldOne(string $namespace): array
    {
        foreach ($this->oldToNewNamespaces as $oldNamespace => $newNamespace) {
            if (Strings::startsWith($namespace, $oldNamespace)) {
                return [$oldNamespace, $newNamespace];
            }
        }

        return false;
    }
}
