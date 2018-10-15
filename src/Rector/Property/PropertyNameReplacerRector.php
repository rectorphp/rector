<?php declare(strict_types=1);

namespace Rector\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
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
     * @param string[][] $perClassOldToNewProperties
     */
    public function __construct(array $perClassOldToNewProperties, IdentifierRenamer $identifierRenamer)
    {
        $this->perClassOldToNewProperties = $perClassOldToNewProperties;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined old properties by new ones.', [
            new ConfiguredCodeSample(
                '$someObject->someOldProperty;',
                '$someObject->someNewProperty;',
                [
                    '$perClassOldToNewProperties' => [
                        'SomeClass' => [
                            'someOldProperty' => 'someNewProperty',
                        ],
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
        return [PropertyFetch::class];
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $this->activeTypes = [];
        $matchedTypes = $this->matchTypes($propertyFetchNode, $this->getClasses());

        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;
        }

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
