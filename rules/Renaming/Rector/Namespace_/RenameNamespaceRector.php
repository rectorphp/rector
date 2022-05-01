<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\NamespaceMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNamespaceRenamer;
use Rector\Renaming\ValueObject\RenamedNamespace;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Namespace_\RenameNamespaceRector\RenameNamespaceRectorTest
 */
final class RenameNamespaceRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var array<class-string<Node>>
     */
    private const ONLY_CHANGE_DOCBLOCK_NODE = [\PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\Expression::class, \PhpParser\Node\Stmt\ClassLike::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class];
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
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNamespaceRenamer
     */
    private $docBlockNamespaceRenamer;
    public function __construct(\Rector\Naming\NamespaceMatcher $namespaceMatcher, \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNamespaceRenamer $docBlockNamespaceRenamer)
    {
        $this->namespaceMatcher = $namespaceMatcher;
        $this->docBlockNamespaceRenamer = $docBlockNamespaceRenamer;
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
        $item3Unpacked = self::ONLY_CHANGE_DOCBLOCK_NODE;
        return \array_merge([\PhpParser\Node\Stmt\Namespace_::class, \PhpParser\Node\Stmt\Use_::class, \PhpParser\Node\Name::class], $item3Unpacked);
    }
    /**
     * @param Namespace_|Use_|Name|Property|ClassMethod|Function_|Expression|ClassLike|FileWithoutNamespace $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (\in_array(\get_class($node), self::ONLY_CHANGE_DOCBLOCK_NODE, \true)) {
            /** @var Property|ClassMethod|Function_|Expression|ClassLike|FileWithoutNamespace $node */
            return $this->docBlockNamespaceRenamer->renameFullyQualifiedNamespace($node, $this->oldToNewNamespaces);
        }
        /** @var Namespace_|Use_|Name $node */
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
        \RectorPrefix20220501\Webmozart\Assert\Assert::allStringNotEmpty(\array_keys($configuration));
        \RectorPrefix20220501\Webmozart\Assert\Assert::allStringNotEmpty($configuration);
        /** @var array<string, string> $configuration */
        $this->oldToNewNamespaces = $configuration;
    }
    private function processFullyQualified(\PhpParser\Node\Name $name, \Rector\Renaming\ValueObject\RenamedNamespace $renamedNamespace) : ?\PhpParser\Node\Name\FullyQualified
    {
        if (\strncmp($name->toString(), $renamedNamespace->getNewNamespace() . '\\', \strlen($renamedNamespace->getNewNamespace() . '\\')) === 0) {
            return null;
        }
        $nameInNewNamespace = $renamedNamespace->getNameInNewNamespace();
        $values = \array_values($this->oldToNewNamespaces);
        if (!isset($this->isChangedInNamespaces[$nameInNewNamespace])) {
            return new \PhpParser\Node\Name\FullyQualified($nameInNewNamespace);
        }
        if (!\in_array($nameInNewNamespace, $values, \true)) {
            return new \PhpParser\Node\Name\FullyQualified($nameInNewNamespace);
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
}
