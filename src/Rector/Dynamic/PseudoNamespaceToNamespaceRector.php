<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Builder\StatementGlue;
use Rector\Node\Attribute;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;

final class PseudoNamespaceToNamespaceRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $pseudoNamespacePrefixes = [];

    /**
     * @var string[]
     */
    private $oldToNewUseStatements = [];

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
     * @param string[] $configuration
     */
    public function __construct(array $configuration, NodeFactory $nodeFactory, StatementGlue $statementGlue)
    {
        $this->resolvePseudoNamespacePrefixesAndExcludedClasses($configuration);
        $this->nodeFactory = $nodeFactory;
        $this->statementGlue = $statementGlue;
    }

    public function isCandidate(Node $node): bool
    {
        $name = $this->resolveNameFromNode($node);
        if ($name === null) {
            return false;
        }

        if (in_array($name, $this->excludedClasses, true)) {
            return false;
        }

        foreach ($this->pseudoNamespacePrefixes as $pseudoNamespacePrefix) {
            if (Strings::startsWith($name, $pseudoNamespacePrefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Name|Identifier $nameOrIdentifierNode
     */
    public function refactor(Node $nameOrIdentifierNode): ?Node
    {
        $oldName = $this->resolveNameFromNode($nameOrIdentifierNode);
        $newNameParts = explode('_', $oldName);
        $parentNode = $nameOrIdentifierNode->getAttribute(Attribute::PARENT_NODE);
        $lastNewNamePart = $newNameParts[count($newNameParts) - 1];

        if ($nameOrIdentifierNode instanceof Name) {
            if ($parentNode instanceof UseUse) {
                $this->oldToNewUseStatements[$oldName] = $lastNewNamePart;
            } elseif (isset($this->oldToNewUseStatements[$oldName])) {
                $newNameParts = [$this->oldToNewUseStatements[$oldName]];
            }

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
     * @param Node[] $nodes
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
        $this->oldToNewUseStatements = [];

        return $nodes;
    }

    private function resolveNameFromNode(Node $node): ?string
    {
        if ($node instanceof Identifier || $node instanceof Name) {
            return $node->toString();
        }

        return null;
    }

    /**
     * @param string[] $configuration
     */
    private function resolvePseudoNamespacePrefixesAndExcludedClasses(array $configuration): void
    {
        foreach ($configuration as $item) {
            if (Strings::startsWith($item, '!')) {
                $this->excludedClasses[] = ltrim($item, '!');
            } else {
                $this->pseudoNamespacePrefixes[] = $item;
            }
        }
    }
}
