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
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\NamespaceMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockNamespaceRenamer;
use Rector\Renaming\ValueObject\RenamedNamespace;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202305\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\Namespace_\RenameNamespaceRector\RenameNamespaceRectorTest
 */
final class RenameNamespaceRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<class-string<Node>>
     */
    private const ONLY_CHANGE_DOCBLOCK_NODE = [Property::class, ClassMethod::class, Function_::class, Expression::class, ClassLike::class];
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
    public function __construct(NamespaceMatcher $namespaceMatcher, DocBlockNamespaceRenamer $docBlockNamespaceRenamer)
    {
        $this->namespaceMatcher = $namespaceMatcher;
        $this->docBlockNamespaceRenamer = $docBlockNamespaceRenamer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces old namespace by new one.', [new ConfiguredCodeSample('$someObject = new SomeOldNamespace\\SomeClass;', '$someObject = new SomeNewNamespace\\SomeClass;', ['SomeOldNamespace' => 'SomeNewNamespace'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        $item3Unpacked = self::ONLY_CHANGE_DOCBLOCK_NODE;
        return \array_merge([Namespace_::class, Use_::class, Name::class], $item3Unpacked);
    }
    /**
     * @param Namespace_|Use_|Name|Property|ClassMethod|Function_|Expression|ClassLike $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (\in_array(\get_class($node), self::ONLY_CHANGE_DOCBLOCK_NODE, \true)) {
            /** @var Property|ClassMethod|Function_|Expression|ClassLike $node */
            return $this->docBlockNamespaceRenamer->renameFullyQualifiedNamespace($node, $this->oldToNewNamespaces);
        }
        /** @var Namespace_|Use_|Name $node */
        $name = $this->getName($node);
        if ($name === null) {
            return null;
        }
        $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace($name, $this->oldToNewNamespaces);
        if (!$renamedNamespaceValueObject instanceof RenamedNamespace) {
            return null;
        }
        if ($this->isClassFullyQualifiedName($node)) {
            return null;
        }
        if ($node instanceof Namespace_) {
            $newName = $renamedNamespaceValueObject->getNameInNewNamespace();
            $node->name = new Name($newName);
            $this->isChangedInNamespaces[$newName] = \true;
            return $node;
        }
        if ($node instanceof Use_) {
            $newName = $renamedNamespaceValueObject->getNameInNewNamespace();
            $node->uses[0]->name = new Name($newName);
            return $node;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        // already resolved above
        if ($parentNode instanceof Namespace_) {
            return null;
        }
        if (!$parentNode instanceof UseUse) {
            return $this->processFullyQualified($node, $renamedNamespaceValueObject);
        }
        if ($parentNode->type !== Use_::TYPE_UNKNOWN) {
            return $this->processFullyQualified($node, $renamedNamespaceValueObject);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allStringNotEmpty(\array_keys($configuration));
        Assert::allStringNotEmpty($configuration);
        /** @var array<string, string> $configuration */
        $this->oldToNewNamespaces = $configuration;
    }
    private function processFullyQualified(Name $name, RenamedNamespace $renamedNamespace) : ?FullyQualified
    {
        if (\strncmp($name->toString(), $renamedNamespace->getNewNamespace() . '\\', \strlen($renamedNamespace->getNewNamespace() . '\\')) === 0) {
            return null;
        }
        $nameInNewNamespace = $renamedNamespace->getNameInNewNamespace();
        $values = \array_values($this->oldToNewNamespaces);
        if (!isset($this->isChangedInNamespaces[$nameInNewNamespace])) {
            return new FullyQualified($nameInNewNamespace);
        }
        if (!\in_array($nameInNewNamespace, $values, \true)) {
            return new FullyQualified($nameInNewNamespace);
        }
        return null;
    }
    /**
     * Checks for "new \ClassNoNamespace;"
     * This should be skipped, not a namespace.
     */
    private function isClassFullyQualifiedName(Node $node) : bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            return \false;
        }
        if (!$parentNode instanceof New_) {
            return \false;
        }
        /** @var FullyQualified $fullyQualifiedNode */
        $fullyQualifiedNode = $parentNode->class;
        $newClassName = $fullyQualifiedNode->toString();
        return \array_key_exists($newClassName, $this->oldToNewNamespaces);
    }
}
