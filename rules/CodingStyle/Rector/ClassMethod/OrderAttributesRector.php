<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\OrderAttributesRectorTest
 */
final class OrderAttributesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ATTRIBUTES_ORDER = 'attributes_order';

    /**
     * @var array<string, int>
     */
    private array $attributesOrderByName = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Order attributes by desired names', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
#[Second]
#[First]
class Someclass
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
#[First]
#[Second]
class Someclass
{
}
CODE_SAMPLE
,
                [
                    self::ATTRIBUTES_ORDER => ['First', 'Second'],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            Class_::class,
            Property::class,
            Param::class,
            ClassMethod::class,
            Function_::class,
            Closure::class,
            ArrowFunction::class,
        ];
    }

    /**
     * @param ClassMethod|Property|Function_|Closure|Param|Class_|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->attrGroups === []) {
            return null;
        }

        $originalAttrGroups = $node->attrGroups;
        $currentAttrGroups = $originalAttrGroups;

        usort($currentAttrGroups, function (
            AttributeGroup $firstAttributeGroup,
            AttributeGroup $secondAttributeGroup,
        ): int {
            $firstAttributePosition = $this->resolveAttributeGroupPosition($firstAttributeGroup);
            $secondAttributePosition = $this->resolveAttributeGroupPosition($secondAttributeGroup);

            return $firstAttributePosition <=> $secondAttributePosition;
        });

        if ($currentAttrGroups === $originalAttrGroups) {
            return null;
        }

        $node->attrGroups = $currentAttrGroups;
        return $node;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $attributesOrder = $configuration[self::ATTRIBUTES_ORDER] ?? [];
        Assert::allString($attributesOrder);

        $this->attributesOrderByName = array_flip($attributesOrder);
    }

    private function resolveAttributeGroupPosition(AttributeGroup $attributeGroup): int
    {
        $attrName = $this->getName($attributeGroup->attrs[0]->name);

        // 1000 makes the attribute last, as positioned attributes have a higher priority
        return $this->attributesOrderByName[$attrName] ?? 1000;
    }
}
