<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Node\Attribute;
use Rector\NodeVisitor\Collector\NodeCollector;
use Rector\Rector\AbstractRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\NamespaceAnalyzer;

final class ClassReplacerRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var NodeCollector
     */
    private $nodeCollector;

    /**
     * @param string[] $oldToNewClasses
     */
    public function __construct(
        array $oldToNewClasses,
        NamespaceAnalyzer $namespaceAnalyzer,
        NodeCollector $nodeCollector
    ) {
        $this->oldToNewClasses = $oldToNewClasses;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
        $this->nodeCollector = $nodeCollector;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Name && ! $node instanceof Use_) {
            return false;
        }

        $nameNode = $this->resolveNameNodeFromNode($node);

        return isset($this->oldToNewClasses[$nameNode->toString()]);
    }

    /**
     * @param Name|UseUse $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Name) {
            $newName = $this->resolveNewNameFromNode($node);
            $newNameNode = new Name($newName);

            return new Name($newNameNode->getLast());
        }

        if ($node instanceof Use_) {
            $newName = $this->resolveNewNameFromNode($node);

            if ($this->namespaceAnalyzer->isUseStatmenetAlreadyPresent($node, $newName)) {
                $this->nodeCollector->addNodeToRemove($node);

                return null;
//                $this->shouldRemoveNode = true;
            }

            $node->uses[0]->name = new Name($newName);

            $node->setAttribute(Attribute::ORIGINAL_NODE, null);

            return $node;
        }

        return null;
    }

    private function resolveNewNameFromNode(Node $node): string
    {
        $nameNode = $this->resolveNameNodeFromNode($node);

        return $this->oldToNewClasses[$nameNode->toString()];
    }

    private function resolveNameNodeFromNode(Node $node): ?Name
    {
        if ($node instanceof Name) {
            // resolved name has priority, as it is FQN
            $resolvedName = $node->getAttribute(Attribute::RESOLVED_NAME);
            if ($resolvedName instanceof FullyQualified) {
                return $resolvedName;
            }

            return $node;
        }

        if ($node instanceof Use_) {
            return $node->uses[0]->name;
        }

        return null;
    }
}
