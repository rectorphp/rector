<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\RenamedNamespace;
use Rector\Naming\NamespaceMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector\RenameNamespaceRectorTest
 */
final class RenameNamespaceRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_NAMESPACES = '$oldToNewNamespaces';

    /**
     * @var string[]
     */
    private $oldToNewNamespaces = [];

    /**
     * @var NamespaceMatcher
     */
    private $namespaceMatcher;

    public function __construct(NamespaceMatcher $namespaceMatcher)
    {
        $this->namespaceMatcher = $namespaceMatcher;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces old namespace by new one.', [
            new ConfiguredCodeSample(
                '$someObject = new SomeOldNamespace\SomeClass;',
                '$someObject = new SomeNewNamespace\SomeClass;',
                [
                    self::OLD_TO_NEW_NAMESPACES => [
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
        $name = $this->getName($node);
        if ($name === null) {
            return null;
        }

        $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace($name, $this->oldToNewNamespaces);
        if (! $renamedNamespaceValueObject instanceof RenamedNamespace) {
            return null;
        }

        if ($this->isClassFullyQualifiedName($node)) {
            return null;
        }

        if ($node instanceof Namespace_) {
            $newName = $renamedNamespaceValueObject->getNameInNewNamespace();
            $node->name = new Name($newName);

            return $node;
        }

        if ($node instanceof Use_) {
            $newName = $renamedNamespaceValueObject->getNameInNewNamespace();
            $node->uses[0]->name = new Name($newName);

            return $node;
        }

        $newName = $this->isPartialNamespace($node) ? $this->resolvePartialNewName(
            $node,
            $renamedNamespaceValueObject
        ) : $renamedNamespaceValueObject->getNameInNewNamespace();

        return new FullyQualified($newName);
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->oldToNewNamespaces = $configuration[self::OLD_TO_NEW_NAMESPACES] ?? [];
    }

    /**
     * Checks for "new \ClassNoNamespace;"
     * This should be skipped, not a namespace.
     */
    private function isClassFullyQualifiedName(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            return false;
        }

        if (! $parentNode instanceof New_) {
            return false;
        }

        /** @var FullyQualified $fullyQualifiedNode */
        $fullyQualifiedNode = $parentNode->class;

        $newClassName = $fullyQualifiedNode->toString();

        return array_key_exists($newClassName, $this->oldToNewNamespaces);
    }

    private function isPartialNamespace(Name $name): bool
    {
        $resolvedName = $name->getAttribute(AttributeKey::RESOLVED_NAME);
        if (! $resolvedName instanceof Name) {
            return false;
        }

        if ($resolvedName instanceof FullyQualified) {
            return ! $this->isName($name, $resolvedName->toString());
        }

        return false;
    }

    private function resolvePartialNewName(Name $name, RenamedNamespace $renamedNamespace): string
    {
        $nameInNewNamespace = $renamedNamespace->getNameInNewNamespace();

        // first dummy implementation - improve
        $cutOffFromTheLeft = Strings::length($nameInNewNamespace) - Strings::length($name->toString());

        return Strings::substring($nameInNewNamespace, $cutOffFromTheLeft);
    }
}
