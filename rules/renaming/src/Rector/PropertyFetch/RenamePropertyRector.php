<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\PropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\ValueObject\RenameProperty;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Renaming\Tests\Rector\PropertyFetch\RenamePropertyRector\RenamePropertyRectorTest
 */
final class RenamePropertyRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const RENAMED_PROPERTIES = 'old_to_new_property_by_types';

    /**
     * @var RenameProperty[]
     */
    private $renamedProperties = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces defined old properties by new ones.', [
            new ConfiguredCodeSample(
                '$someObject->someOldProperty;',
                '$someObject->someNewProperty;',
                [
                    self::RENAMED_PROPERTIES => [
                        new RenameProperty('SomeClass', 'someOldProperty', 'someNewProperty'),
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
        foreach ($this->renamedProperties as $renamedProperty) {
            if (! $this->isObjectType($node->var, $renamedProperty->getType())) {
                continue;
            }

            if (! $this->isName($node, $renamedProperty->getOldProperty())) {
                continue;
            }

            $node->name = new Identifier($renamedProperty->getNewProperty());
            return $node;
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $renamedProperties = $configuration[self::RENAMED_PROPERTIES] ?? [];
        Assert::allIsInstanceOf($renamedProperties, RenameProperty::class);
        $this->renamedProperties = $renamedProperties;
    }
}
