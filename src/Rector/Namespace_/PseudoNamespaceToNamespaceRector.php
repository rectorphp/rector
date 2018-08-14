<?php declare(strict_types=1);

namespace Rector\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use Rector\Builder\StatementGlue;
use Rector\Node\NodeFactory;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PseudoNamespaceToNamespaceRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $pseudoNamespacePrefixes = [];

    /**
     * @var string|null
     */
    private $newNamespace;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var StatementGlue
     */
    private $statementGlue;

    /**
     * @var string[]
     */
    private $excludedClasses = [];

    /**
     * @param string[] $pseudoNamespacePrefixes
     * @param string[] $excludedClasses
     */
    public function __construct(
        array $pseudoNamespacePrefixes,
        array $excludedClasses,
        NodeFactory $nodeFactory,
        StatementGlue $statementGlue
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->statementGlue = $statementGlue;
        $this->pseudoNamespacePrefixes = $pseudoNamespacePrefixes;
        $this->excludedClasses = $excludedClasses;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined Pseudo_Namespaces by Namespace\Ones.', [
            new ConfiguredCodeSample(
                '$someService = Some_Object;',
                '$someService = Some\Object;',
                [
                    '$pseudoNamespacePrefixes' => ['Some_'],
                    '$excludedClasses' => [],
                ]
            ),
        ]);
    }

    public function getNodeType(): string
    {
        return [Name::class, Identifier::class];
    }

    /**
     * @param Name|Identifier $nameOrIdentifierNode
     */
    public function refactor(Node $nameOrIdentifierNode): ?Node
    {
        $name = $this->resolveNameFromNode($nameOrIdentifierNode);
        if ($name === null) {
            return null;
        }
        if (in_array($name, $this->excludedClasses, true)) {
            return null;
        }
        foreach ($this->pseudoNamespacePrefixes as $pseudoNamespacePrefix) {
            if (Strings::startsWith($name, $pseudoNamespacePrefix)) {
            }
        }
        return null;
        $oldName = $this->resolveNameFromNode($nameOrIdentifierNode);

        $newNameParts = explode('_', $oldName);
        $parentNode = $nameOrIdentifierNode->getAttribute(Attribute::PARENT_NODE);
        $lastNewNamePart = $newNameParts[count($newNameParts) - 1];

        if ($nameOrIdentifierNode instanceof Name) {
            $nameOrIdentifierNode->parts = $newNameParts;

            return $nameOrIdentifierNode;
        }

        if ($nameOrIdentifierNode instanceof Identifier && $parentNode instanceof Class_) {
            $namespaceParts = $newNameParts;
            array_pop($namespaceParts);

            $this->newNamespace = implode('\\', $namespaceParts);

            $nameOrIdentifierNode->name = $lastNewNamePart;

            return $nameOrIdentifierNode;
        }

        return null;
    }

    /**
     * @param Stmt[] $nodes
     * @return Node[]
     */
    public function afterTraverse(array $nodes): array
    {
        if ($this->newNamespace) {
            $namespaceNode = $this->nodeFactory->createNamespace($this->newNamespace);

            foreach ($nodes as $key => $node) {
                if ($node instanceof Class_) {
                    $nodes = $this->statementGlue->insertBeforeAndFollowWithNewline($nodes, $namespaceNode, $key);

                    break;
                }
            }
        }

        $this->newNamespace = null;

        return $nodes;
    }

    private function resolveNameFromNode(Node $node): ?string
    {
        if ($node instanceof Identifier || $node instanceof Name) {
            return $node->toString();
        }

        return null;
    }
}
