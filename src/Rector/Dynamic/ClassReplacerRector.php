<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ClassReplacerRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewClasses = [];

    /**
     * @param string[] $oldToNewClasses
     */
    public function __construct(array $oldToNewClasses)
    {
        $this->oldToNewClasses = $oldToNewClasses;
    }

    /**
     * @todo complete list with all possibilities
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined classes by new ones.', [
            new CodeSample('$value = new SomeOldClass;', '$value = new SomeNewClass;'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Name) {
            return false;
        }

        $nameNode = $this->resolveNameNodeFromNode($node);
        if ($nameNode === null) {
            return false;
        }

        return isset($this->oldToNewClasses[$nameNode->toString()]);
    }

    /**
     * @param Name $nameNode
     */
    public function refactor(Node $nameNode): ?Node
    {
        if ($nameNode instanceof Name) {
            $newName = $this->resolveNewNameFromNode($nameNode);

            return new FullyQualified($newName);
        }

        return null;
    }

    private function resolveNewNameFromNode(Node $node): string
    {
        $nameNode = $this->resolveNameNodeFromNode($node);

        if ($nameNode !== null) {
            return $this->oldToNewClasses[$nameNode->toString()];
        }
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
