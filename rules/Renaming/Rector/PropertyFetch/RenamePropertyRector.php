<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\PropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\Type\ThisType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Renaming\ValueObject\RenameProperty;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Renaming\Rector\PropertyFetch\RenamePropertyRector\RenamePropertyRectorTest
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
    private array $renamedProperties = [];

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
     * @return array<class-string<Node>>
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
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        foreach ($this->renamedProperties as $renamedProperty) {
            if (! $this->isObjectType($node->var, $renamedProperty->getObjectType())) {
                continue;
            }

            $oldProperty = $renamedProperty->getOldProperty();
            if (! $this->isName($node, $oldProperty)) {
                continue;
            }

            $nodeVarType = $this->nodeTypeResolver->resolve($node->var);
            if ($nodeVarType instanceof ThisType && $class instanceof ClassLike) {
                $property = $class->getProperty($oldProperty);
                if ($property instanceof Property) {
                    $property->props[0]->name = new VarLikeIdentifier($renamedProperty->getNewProperty());
                }
            }

            $node->name = new Identifier($renamedProperty->getNewProperty());
            return $node;
        }

        return null;
    }

    /**
     * @param array<string, RenameProperty[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $renamedProperties = $configuration[self::RENAMED_PROPERTIES] ?? [];
        Assert::allIsInstanceOf($renamedProperties, RenameProperty::class);
        $this->renamedProperties = $renamedProperties;
    }
}
