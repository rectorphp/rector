<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Node\Attribute;
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
        krsort($oldToNewNamespaces);

        $this->oldToNewNamespaces = $oldToNewNamespaces;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isGivenKind($node, [Namespace_::class, Use_::class, Name::class, FullyQualified::class])) {
            return false;
        }

        $name = $this->resolveNameFromNode($node);
        if (! $this->isNamespaceToChange($name)) {
            return false;
        }

        return ! $this->isClassFullyQualifiedName($node);
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

        if ($node instanceof Name) {
            if ($this->isPartialNamespace($node)) {
                $newName = $this->resolvePartialNewName($node);
            } else {
                $newName = $this->resolveNewNameFromNode($node);
            }

            $node->parts = explode('\\', $newName);
            $node->setAttribute('origNode', null);

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

        if ($node instanceof Name) {
            /** @var FullyQualified|null $resolveName */
            $resolveName = $node->getAttribute(Attribute::RESOLVED_NAME);
            if ($resolveName) {
                return $resolveName->toString();
            }

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

    /**
     * Checks for "new \ClassNoNamespace;"
     * This should be skipped, not a namespace.
     */
    private function isClassFullyQualifiedName(Node $node): bool
    {
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode === null) {
            return false;
        }

        if (! $parentNode instanceof New_) {
            return false;
        }

        $newClassName = $parentNode->class->toString();
        foreach ($this->oldToNewNamespaces as $oldNamespace => $newNamespace) {
            if ($newClassName === $oldNamespace) {
                return true;
            }
        }

        return false;
    }

    private function isPartialNamespace(Name $nameNode): bool
    {
        $resolvedName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
        if ($resolvedName === null) {
            return false;
        }

        $nodeName = $nameNode->toString();
        if ($resolvedName instanceof FullyQualified) {
            return $nodeName !== $resolvedName->toString();
        }

        return false;
    }

    private function resolvePartialNewName(Name $nameNode): string
    {
        /** @var FullyQualified $resolvedName */
        $resolvedName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
        $fullyQualifiedName = $resolvedName->toString();
        $completeNewName = $this->resolveNewNameFromNode($resolvedName);

        // first dummy implementation - improve
        $cutOffFromTheLeft = strlen($fullyQualifiedName) - strlen($nameNode->toString());

        return substr($completeNewName, $cutOffFromTheLeft);
    }
}
