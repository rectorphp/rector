<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Namespace_;

use RectorPrefix20211231\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\NamespaceMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Renaming\ValueObject\RenamedNamespace;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211231\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Namespace_\RenameNamespaceRector\RenameNamespaceRectorTest
 */
final class RenameNamespaceRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const OLD_TO_NEW_NAMESPACES = 'old_to_new_namespaces';
    /**
     * @var array<string, string>
     */
    private $oldToNewNamespaces = [];
    /**
     * @var array<string, bool>
     */
    private $isChangedInNamespaces = [];
    /**
     * @readonly
     * @var \Rector\Naming\NamespaceMatcher
     */
    private $namespaceMatcher;
    public function __construct(\Rector\Naming\NamespaceMatcher $namespaceMatcher)
    {
        $this->namespaceMatcher = $namespaceMatcher;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces old namespace by new one.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample('$someObject = new SomeOldNamespace\\SomeClass;', '$someObject = new SomeNewNamespace\\SomeClass;', ['SomeOldNamespace' => 'SomeNewNamespace'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Namespace_::class, \PhpParser\Node\Stmt\Use_::class, \PhpParser\Node\Name::class];
    }
    /**
     * @param Namespace_|Use_|Name $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $name = $this->getName($node);
        if ($name === null) {
            return null;
        }
        $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace($name, $this->oldToNewNamespaces);
        if (!$renamedNamespaceValueObject instanceof \Rector\Renaming\ValueObject\RenamedNamespace) {
            return null;
        }
        if ($this->isClassFullyQualifiedName($node)) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
            $newName = $renamedNamespaceValueObject->getNameInNewNamespace();
            $node->name = new \PhpParser\Node\Name($newName);
            $this->isChangedInNamespaces[$newName] = \true;
            return $node;
        }
        if ($node instanceof \PhpParser\Node\Stmt\Use_) {
            $newName = $renamedNamespaceValueObject->getNameInNewNamespace();
            $node->uses[0]->name = new \PhpParser\Node\Name($newName);
            return $node;
        }
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // already resolved above
        if ($parent instanceof \PhpParser\Node\Stmt\Namespace_) {
            return null;
        }
        if (!$parent instanceof \PhpParser\Node\Stmt\UseUse) {
            return $this->processFullyQualified($node, $renamedNamespaceValueObject);
        }
        if ($parent->type !== \PhpParser\Node\Stmt\Use_::TYPE_UNKNOWN) {
            return $this->processFullyQualified($node, $renamedNamespaceValueObject);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $oldToNewNamespaces = $configuration[self::OLD_TO_NEW_NAMESPACES] ?? $configuration;
        \RectorPrefix20211231\Webmozart\Assert\Assert::allString(\array_keys($oldToNewNamespaces));
        \RectorPrefix20211231\Webmozart\Assert\Assert::allString($oldToNewNamespaces);
        /** @var array<string, string> $oldToNewNamespaces */
        $this->oldToNewNamespaces = $oldToNewNamespaces;
    }
    private function processFullyQualified(\PhpParser\Node\Name $name, \Rector\Renaming\ValueObject\RenamedNamespace $renamedNamespace) : ?\PhpParser\Node\Name\FullyQualified
    {
        $newName = $this->isPartialNamespace($name) ? $this->resolvePartialNewName($name, $renamedNamespace) : $renamedNamespace->getNameInNewNamespace();
        $values = \array_values($this->oldToNewNamespaces);
        if (!isset($this->isChangedInNamespaces[$newName])) {
            return new \PhpParser\Node\Name\FullyQualified($newName);
        }
        if (!\in_array($newName, $values, \true)) {
            return new \PhpParser\Node\Name\FullyQualified($newName);
        }
        return null;
    }
    /**
     * Checks for "new \ClassNoNamespace;"
     * This should be skipped, not a namespace.
     */
    private function isClassFullyQualifiedName(\PhpParser\Node $node) : bool
    {
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            return \false;
        }
        if (!$parentNode instanceof \PhpParser\Node\Expr\New_) {
            return \false;
        }
        /** @var FullyQualified $fullyQualifiedNode */
        $fullyQualifiedNode = $parentNode->class;
        $newClassName = $fullyQualifiedNode->toString();
        return \array_key_exists($newClassName, $this->oldToNewNamespaces);
    }
    private function isPartialNamespace(\PhpParser\Node\Name $name) : bool
    {
        $resolvedName = $name->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::RESOLVED_NAME);
        if (!$resolvedName instanceof \PhpParser\Node\Name) {
            return \false;
        }
        if ($resolvedName instanceof \PhpParser\Node\Name\FullyQualified) {
            return !$this->isName($name, $resolvedName->toString());
        }
        return \false;
    }
    private function resolvePartialNewName(\PhpParser\Node\Name $name, \Rector\Renaming\ValueObject\RenamedNamespace $renamedNamespace) : string
    {
        $nameInNewNamespace = $renamedNamespace->getNameInNewNamespace();
        // first dummy implementation - improve
        $cutOffFromTheLeft = \RectorPrefix20211231\Nette\Utils\Strings::length($nameInNewNamespace) - \RectorPrefix20211231\Nette\Utils\Strings::length($name->toString());
        return \RectorPrefix20211231\Nette\Utils\Strings::substring($nameInNewNamespace, $cutOffFromTheLeft);
    }
}
