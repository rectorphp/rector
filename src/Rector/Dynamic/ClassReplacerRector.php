<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Node\Attribute;
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
     * @param string[] $oldToNewClasses
     */
    public function __construct(array $oldToNewClasses, NamespaceAnalyzer $namespaceAnalyzer)
    {
        $this->oldToNewClasses = $oldToNewClasses;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Name && ! $node instanceof Use_) {
            return false;
        }

        $name = $this->resolveNameFromNode($node);

        return isset($this->oldToNewClasses[$name]);
    }

    /**
     * @param Name|UseUse $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Name) {
            $newName = $this->resolveNewNameFromNode($node);

            return new FullyQualified($newName);
        }

        if ($node instanceof Use_) {
            $newName = $this->resolveNewNameFromNode($node);

            if ($this->namespaceAnalyzer->isUseStatmenetAlreadyPresent($node, $newName)) {
                $this->shouldRemoveNode = true;
            }
        }

        return null;
    }

    private function resolveNewNameFromNode(Node $node): string
    {
        $name = $this->resolveNameFromNode($node);

        return $this->oldToNewClasses[$name];
    }

    private function resolveNameFromNode(Node $node): string
    {
        if ($node instanceof Name) {
            // resolved name has priority, as it is FQN
            $resolvedName = $node->getAttribute(Attribute::RESOLVED_NAME);
            if ($resolvedName instanceof FullyQualified) {
                return $resolvedName->toString();
            }

            return $node->toString();
        }

        if ($node instanceof Use_) {
            return $node->uses[0]->name->toString();
        }

        return '';
    }
}
