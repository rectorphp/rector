<?php

declare (strict_types=1);
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
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\SpecificOrder\OrderAttributesRectorTest
 */
final class OrderAttributesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const ALPHABETICALLY = 'alphabetically';
    /**
     * @var array<string, int>|array<string>
     */
    private $configuration = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Order attributes by desired names', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
#[Second]
#[First]
class Someclass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[First]
#[Second]
class Someclass
{
}
CODE_SAMPLE
, ['First', 'Second']), new ConfiguredCodeSample(<<<'CODE_SAMPLE'
#[BAttribute]
#[AAttribute]
class Someclass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[AAttribute]
#[BAttribute]
class Someclass
{
}
CODE_SAMPLE
, [self::ALPHABETICALLY])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, Property::class, Param::class, ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }
    /**
     * @param ClassMethod|Property|Function_|Closure|Param|Class_|ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->attrGroups === []) {
            return null;
        }
        $originalAttrGroups = $node->attrGroups;
        if ($this->isAlphabetically($this->configuration)) {
            $currentAttrGroups = $this->sortAlphabetically($originalAttrGroups);
        } else {
            $currentAttrGroups = $this->sortBySpecificOrder($originalAttrGroups);
        }
        if ($currentAttrGroups === $originalAttrGroups) {
            return null;
        }
        $node->attrGroups = $currentAttrGroups;
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration = [self::ALPHABETICALLY]) : void
    {
        Assert::allString($configuration);
        Assert::minCount($configuration, 1);
        $this->configuration = $this->isAlphabetically($configuration) ? $configuration : \array_flip($configuration);
    }
    /**
     * @param array<AttributeGroup> $originalAttrGroups
     * @return array<AttributeGroup>
     */
    private function sortAlphabetically(array $originalAttrGroups) : array
    {
        \usort($originalAttrGroups, function (AttributeGroup $firstAttributeGroup, AttributeGroup $secondAttributeGroup) : int {
            $currentNamespace = $this->getName($firstAttributeGroup->attrs[0]->name);
            $nextNamespace = $this->getName($secondAttributeGroup->attrs[0]->name);
            return \strcmp($currentNamespace, $nextNamespace);
        });
        return $originalAttrGroups;
    }
    /**
     * @param array<AttributeGroup> $originalAttrGroups
     * @return array<AttributeGroup>
     */
    private function sortBySpecificOrder(array $originalAttrGroups) : array
    {
        \usort($originalAttrGroups, function (AttributeGroup $firstAttributeGroup, AttributeGroup $secondAttributeGroup) : int {
            $firstAttributePosition = $this->resolveAttributeGroupPosition($firstAttributeGroup);
            $secondAttributePosition = $this->resolveAttributeGroupPosition($secondAttributeGroup);
            return $firstAttributePosition <=> $secondAttributePosition;
        });
        return $originalAttrGroups;
    }
    private function resolveAttributeGroupPosition(AttributeGroup $attributeGroup) : int
    {
        $attrName = $this->getName($attributeGroup->attrs[0]->name);
        return (int) ($this->configuration[$attrName] ?? \count($this->configuration));
    }
    /**
     * @param array<string, int>|array<string> $configuration
     */
    private function isAlphabetically(array $configuration) : bool
    {
        return \count($configuration) === 1 && $configuration[0] === self::ALPHABETICALLY;
    }
}
