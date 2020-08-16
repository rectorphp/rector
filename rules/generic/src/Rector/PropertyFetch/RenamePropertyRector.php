<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\PropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\PropertyFetch\RenamePropertyRector\RenamePropertyRectorTest
 */
final class RenamePropertyRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const OLD_TO_NEW_PROPERTY_BY_TYPES = 'old_to_new_property_by_types';

    /**
     * class => [
     *     oldProperty => newProperty
     * ]
     *
     * @var string[][]
     */
    private $oldToNewPropertyByTypes = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces defined old properties by new ones.', [
            new ConfiguredCodeSample(
                '$someObject->someOldProperty;',
                '$someObject->someNewProperty;',
                [
                    self::OLD_TO_NEW_PROPERTY_BY_TYPES => [
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
            if (! $this->isObjectType($node->var, $type)) {
                continue;
            }

            foreach ($oldToNewProperties as $oldProperty => $newProperty) {
                if (! $this->isName($node, $oldProperty)) {
                    continue;
                }

                $node->name = new Identifier($newProperty);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->oldToNewPropertyByTypes = $configuration[self::OLD_TO_NEW_PROPERTY_BY_TYPES] ?? [];
    }
}
