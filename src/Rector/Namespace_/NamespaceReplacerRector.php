<?php declare(strict_types=1);

namespace Rector\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use function Safe\krsort;
use function Safe\substr;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces old namespace by new one.', [
            new ConfiguredCodeSample(
                '$someObject = new SomeOldNamespace\SomeClass;',
                '$someObject = new SomeNewNamespace\SomeClass;',
                [
                    '$oldToNewNamespaces' => [
                        'SomeOldNamespace' => 'SomeNewNamespace',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class, Use_::class, Name::class];
    }

    /**
     * @param Namespace_|Use_|Name $node
     */
    public function refactor(Node $node): ?Node
    {
        $name = $this->resolveNameFromNode($node);
        if (! $this->isNamespaceToChange($name)) {
            return null;
        }

        if ($this->isClassFullyQualifiedName($node)) {
            return null;
        }

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
        if ($node instanceof Namespace_ && $node->name) {
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

        return '';
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
        /** @var string $oldNamespace */
        foreach ($this->oldToNewNamespaces as $oldNamespace => $newNamespace) {
            if (Strings::startsWith($namespace, $oldNamespace)) {
                return [$oldNamespace, $newNamespace];
            }
        }

        return [];
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

        /** @var FullyQualified $fullyQualifiedNode */
        $fullyQualifiedNode = $parentNode->class;

        $newClassName = $fullyQualifiedNode->toString();
        foreach (array_keys($this->oldToNewNamespaces) as $oldNamespace) {
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
        $completeNewName = $this->resolveNewNameFromNode($resolvedName);

        // first dummy implementation - improve
        $cutOffFromTheLeft = strlen($completeNewName) - strlen($nameNode->toString());

        return substr($completeNewName, $cutOffFromTheLeft);
    }
}
