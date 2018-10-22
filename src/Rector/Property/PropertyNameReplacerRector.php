<?php declare(strict_types=1);

namespace Rector\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
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
    private $oldToNewPropertyByTypes = [];

    /**
     * @param string[][] $oldToNewPropertyByTypes
     */
    public function __construct(array $oldToNewPropertyByTypes)
    {
        $this->oldToNewPropertyByTypes = $oldToNewPropertyByTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined old properties by new ones.', [
            new ConfiguredCodeSample(
                '$someObject->someOldProperty;',
                '$someObject->someNewProperty;',
                [
                    '$oldToNewPropertyByTypes' => [
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
     * @param PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldToNewPropertyByTypes as $type => $oldToNewProperties) {
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($oldToNewProperties as $oldProperty => $newProperty) {
                if (strtolower($this->getName($node)) !== strtolower($oldProperty)) {
                    continue;
                }

                $node->name = new Identifier($newProperty);
            }
        }

        return $node;
    }
}
