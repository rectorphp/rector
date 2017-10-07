<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
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
        if (! $this->isGivenKind($node, [Namespace_::class, Use_::class, FullyQualified::class])) {
            return false;
        }

        $name = $this->resolveNameFromNode($node);

        return $this->isNamespaceToChange($name);
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Namespace_) {
            $newName = $this->resolveNewNameFromNode($node);
            $node->name = new Name($newName);

            return $node;
        }

        if ($node instanceof Use_) {
            $newName = $this->resolveNewNameFromNode($node);

            $node->uses[0]->name = new Name($newName);

            return $node;
        }

        if ($node instanceof FullyQualified) {
            $newName = $this->resolveNewNameFromNode($node);

            $node->parts = explode('\\', $newName);

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

    private function resolveNameFromNode(Node $node): string
    {
        if ($node instanceof Namespace_) {
            return $node->name->toString();
        }

        if ($node instanceof Use_) {
            return $node->uses[0]->name->toString();
        }

        if ($node instanceof FullyQualified) {
            return $node->toString();
        }
    }

    private function isNamespaceToChange(string $namespace): bool
    {
        return (bool) $this->getNewNamespaceForOldOne($namespace);
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

        return [];
    }

    /**
     * @param string[] $types
     */
    private function isGivenKind(Node $node, array $types): bool
    {
        foreach ($types as $type) {
            if (is_a($node, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
