<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class PropertyNameReplacerRector extends AbstractRector
{
    /**
     * class => [
     *     oldProperty => newProperty
     * ]
     *
     * @var string[][]
     */
    private $perClassOldToNewProperties = [];

    /**
     * @var string[]
     */
    private $activeTypes = [];

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @param string[][] $perClassOldToNewProperties
     */
    public function __construct(
        array $perClassOldToNewProperties,
        IdentifierRenamer $identifierRenamer,
        PropertyFetchAnalyzer $propertyFetchAnalyzer
    ) {
        $this->perClassOldToNewProperties = $perClassOldToNewProperties;
        $this->identifierRenamer = $identifierRenamer;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('[Dynamic] Replaces defined old properties by new ones.', [
            new CodeSample('$someObject->someOldProperty;', '$someObject->someNewProperty;'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeTypes = [];

        $matchedTypes = $this->propertyFetchAnalyzer->matchTypes($node, $this->getClasses());
        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;

            return true;
        }

        return false;
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $oldToNewProperties = $this->matchOldToNewProperties();

        /** @var Identifier $identifierNode */
        $identifierNode = $propertyFetchNode->name;

        $propertyName = $identifierNode->toString();

        if (! isset($oldToNewProperties[$propertyName])) {
            return $propertyFetchNode;
        }

        foreach ($oldToNewProperties as $oldProperty => $newProperty) {
            if ($propertyName !== $oldProperty) {
                continue;
            }

            $this->identifierRenamer->renameNode($propertyFetchNode, $newProperty);
        }

        return $propertyFetchNode;
    }

    /**
     * @return string[]
     */
    private function getClasses(): array
    {
        return array_keys($this->perClassOldToNewProperties);
    }

    /**
     * @return string[]
     */
    private function matchOldToNewProperties(): array
    {
        foreach ($this->activeTypes as $activeType) {
            if ($this->perClassOldToNewProperties[$activeType]) {
                return $this->perClassOldToNewProperties[$activeType];
            }
        }

        return [];
    }
}
