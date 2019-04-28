<?php declare(strict_types=1);

namespace Rector\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PseudoNamespaceToNamespaceRector extends AbstractRector
{
    /**
     * @var string|null
     */
    private $newNamespace;

    /**
     * @var string[][]|null[]
     */
    private $namespacePrefixesWithExcludedClasses = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @param string[][]|null[] $namespacePrefixesWithExcludedClasses
     */
    public function __construct(
        ClassManipulator $classManipulator,
        DocBlockManipulator $docBlockManipulator,
        array $namespacePrefixesWithExcludedClasses = []
    ) {
        $this->classManipulator = $classManipulator;
        $this->namespacePrefixesWithExcludedClasses = $namespacePrefixesWithExcludedClasses;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined Pseudo_Namespaces by Namespace\Ones.', [
            new ConfiguredCodeSample(
                '$someService = new Some_Object;',
                '$someService = new Some\Object;',
                [
                    ['Some_' => []],
                ]
            ),
            new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
/** @var Some_Object $someService */
$someService = new Some_Object;
$someClassToKeep = new Some_Class_To_Keep;
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
/** @var Some\Object $someService */
$someService = new Some\Object;
$someClassToKeep = new Some_Class_To_Keep;
CODE_SAMPLE
                ,
                [
                    ['Some_' => ['Some_Class_To_Keep']],
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
            $this->docBlockManipulator->changeUnderscoreType($node, $namespacePrefix, $excludedClasses);
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

        $namespaceNode = new Namespace_(new Name($this->newNamespace));
        foreach ($nodes as $key => $node) {
            if ($node instanceof Class_) {
                $nodes = $this->classManipulator->insertBeforeAndFollowWithNewline($nodes, $namespaceNode, $key);

                break;
            }
        }

        $this->newNamespace = null;

        return $nodes;
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

        $newNameParts = explode('_', $name);
        $lastNewNamePart = $newNameParts[count($newNameParts) - 1];

        $namespaceParts = $newNameParts;
        array_pop($namespaceParts);

        $newNamespace = implode('\\', $namespaceParts);
        if ($this->newNamespace !== null && $this->newNamespace !== $newNamespace) {
            throw new ShouldNotHappenException('There cannot be 2 different namespaces in one file');
        }

        $this->newNamespace = $newNamespace;

        $identifier->name = $lastNewNamePart;

        return $identifier;
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
}
