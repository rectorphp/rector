<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Name;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\PhpDocTypeRenamer;

/**
 * @see \Rector\Generic\Tests\Rector\Name\PseudoNamespaceToNamespaceRector\PseudoNamespaceToNamespaceRectorTest
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
    private const SPLIT_BY_UNDERSCORE_PATTERN = '#([a-zA-Z])(_)?(_)([a-zA-Z])#';

    /**
     * @var PhpDocTypeRenamer
     */
    private $phpDocTypeRenamer;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    /**
     * @var string|null
     */
    private $newNamespace;

    /**
     * @var string[][]|null[]
     */
    private $namespacePrefixesWithExcludedClasses = [];

    public function __construct(
        ClassInsertManipulator $classInsertManipulator,
        PhpDocTypeRenamer $phpDocTypeRenamer
    ) {
        $this->phpDocTypeRenamer = $phpDocTypeRenamer;
        $this->classInsertManipulator = $classInsertManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined Pseudo_Namespaces by Namespace\Ones.', [
            new ConfiguredCodeSample(
<<<'PHP'
/** @var Some_Chicken $someService */
$someService = new Some_Chicken;
$someClassToKeep = new Some_Class_To_Keep;
PHP
                ,
<<<'PHP'
/** @var Some\Chicken $someService */
$someService = new Some\Chicken;
$someClassToKeep = new Some_Class_To_Keep;
PHP
                ,
                [
                    self::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => [
                        'Some_' => ['Some_Class_To_Keep'],
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
        return [Name::class, Identifier::class, Property::class, FunctionLike::class, Expression::class];
    }

    /**
     * @param Name|Identifier|Property|FunctionLike $node
     */
    public function refactor(Node $node): ?Node
    {
        // replace on @var/@param/@return/@throws
        foreach ($this->namespacePrefixesWithExcludedClasses as $namespacePrefix => $excludedClasses) {
            $this->phpDocTypeRenamer->changeUnderscoreType($node, $namespacePrefix, $excludedClasses ?? []);
        }

        if ($node instanceof Name || $node instanceof Identifier) {
            return $this->processNameOrIdentifier($node);
        }

        return null;
    }

    /**
     * @param Stmt[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        if ($this->newNamespace === null) {
            return $nodes;
        }

        $namespace = new Namespace_(new Name($this->newNamespace));
        foreach ($nodes as $key => $node) {
            if ($node instanceof Use_ || $node instanceof Class_) {
                $nodes = $this->classInsertManipulator->insertBefore($nodes, $namespace, $key);

                break;
            }
        }

        $this->newNamespace = null;

        return $nodes;
    }

    public function configure(array $configuration): void
    {
        $this->namespacePrefixesWithExcludedClasses = $configuration[self::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES] ?? [];
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

        foreach ($this->namespacePrefixesWithExcludedClasses as $namespacePrefix => $excludedClasses) {
            if (! $this->isName($node, $namespacePrefix . '*')) {
                continue;
            }

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

        $newNamespace = Strings::replace($namespaceName, self::SPLIT_BY_UNDERSCORE_PATTERN, '$1$2\\\\$4');

        if ($this->newNamespace !== null && $this->newNamespace !== $newNamespace) {
            throw new ShouldNotHappenException('There cannot be 2 different namespaces in one file');
        }

        $this->newNamespace = $newNamespace;

        $identifier->name = $lastNewNamePart;

        return $identifier;
    }
}
