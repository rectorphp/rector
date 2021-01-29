<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\FileWithoutNamespace;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\PhpDocTypeRenamer;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\PseudoNamespaceToNamespaceRectorTest
 */
final class PseudoNamespaceToNamespaceRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES = 'namespace_prefixed_with_excluded_classes';

    /**
     * @see https://regex101.com/r/chvLgs/1/
     * @var string
     */
    private const SPLIT_BY_UNDERSCORE_REGEX = '#([a-zA-Z])(_)?(_)([a-zA-Z])#';

    /**
     * @var PseudoNamespaceToNamespace[]
     */
    private $pseudoNamespacesToNamespaces = [];

    /**
     * @var PhpDocTypeRenamer
     */
    private $phpDocTypeRenamer;

    /**
     * @var string|null
     */
    private $newNamespace;

    public function __construct(PhpDocTypeRenamer $phpDocTypeRenamer)
    {
        $this->phpDocTypeRenamer = $phpDocTypeRenamer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces defined Pseudo_Namespaces by Namespace\Ones.', [
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
/** @var Some_Chicken $someService */
$someService = new Some_Chicken;
$someClassToKeep = new Some_Class_To_Keep;
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
/** @var Some\Chicken $someService */
$someService = new Some\Chicken;
$someClassToKeep = new Some_Class_To_Keep;
CODE_SAMPLE
                ,
                [
                    self::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => [
                        new PseudoNamespaceToNamespace('Some_', ['Some_Class_To_Keep']),
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
        // property, method
        return [FileWithoutNamespace::class, Namespace_::class];
    }

    /**
     * @param Namespace_|FileWithoutNamespace $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->newNamespace = null;

        if ($node instanceof FileWithoutNamespace) {
            $stmts = $this->refactorStmts($node->stmts);
            $node->stmts = $stmts;

            // add a new namespace?
            if ($this->newNamespace) {
                $namespace = new Namespace_(new Name($this->newNamespace));
                $namespace->stmts = $stmts;

                return $namespace;
            }
        }

        if ($node instanceof Namespace_) {
            $this->refactorStmts([$node]);
            return $node;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $namespacePrefixesWithExcludedClasses = $configuration[self::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES] ?? [];
        Assert::allIsInstanceOf($namespacePrefixesWithExcludedClasses, PseudoNamespaceToNamespace::class);

        $this->pseudoNamespacesToNamespaces = $namespacePrefixesWithExcludedClasses;
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function refactorStmts(array $stmts): array
    {
        $this->traverseNodesWithCallable($stmts, function (Node $node): ?Node {
            if (! StaticInstanceOf::isOneOf(
                $node,
                [Name::class, Identifier::class, Property::class, FunctionLike::class])) {
                return null;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

            // replace on @var/@param/@return/@throws
            foreach ($this->pseudoNamespacesToNamespaces as $namespacePrefixWithExcludedClasses) {
                $this->phpDocTypeRenamer->changeUnderscoreType($phpDocInfo, $node, $namespacePrefixWithExcludedClasses);
            }
            if ($node instanceof Name) {
                return $this->processNameOrIdentifier($node);
            }
            if ($node instanceof Identifier) {
                return $this->processNameOrIdentifier($node);
            }

            return null;
        });

        return $stmts;
    }

    /**
     * @param Name|Identifier $node
     * @return Name|Identifier
     */
    private function processNameOrIdentifier(Node $node): ?Node
    {
        // no name → skip
        if ($node->toString() === '') {
            return null;
        }

        foreach ($this->pseudoNamespacesToNamespaces as $pseudoNamespacesToNamespace) {
            if (! $this->isName($node, $pseudoNamespacesToNamespace->getNamespacePrefix() . '*')) {
                continue;
            }

            $excludedClasses = $pseudoNamespacesToNamespace->getExcludedClasses();
            if (is_array($excludedClasses) && $this->isNames($node, $excludedClasses)) {
                return null;
            }

            if ($node instanceof Name) {
                return $this->processName($node);
            }

            return $this->processIdentifier($node);
        }

        return null;
    }

    private function processName(Name $name): Name
    {
        $nodeName = $this->getName($name);

        if ($nodeName !== null) {
            $name->parts = explode('_', $nodeName);
        }

        return $name;
    }

    private function processIdentifier(Identifier $identifier): ?Identifier
    {
        $parentNode = $identifier->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Class_) {
            return null;
        }

        $name = $this->getName($identifier);
        if ($name === null) {
            return null;
        }

        /** @var string $namespaceName */
        $namespaceName = Strings::before($name, '_', -1);

        /** @var string $lastNewNamePart */
        $lastNewNamePart = Strings::after($name, '_', -1);

        $newNamespace = Strings::replace($namespaceName, self::SPLIT_BY_UNDERSCORE_REGEX, '$1$2\\\\$4');

        if ($this->newNamespace !== null && $this->newNamespace !== $newNamespace) {
            throw new ShouldNotHappenException('There cannot be 2 different namespaces in one file');
        }

        $this->newNamespace = $newNamespace;
        $identifier->name = $lastNewNamePart;

        return $identifier;
    }
}
