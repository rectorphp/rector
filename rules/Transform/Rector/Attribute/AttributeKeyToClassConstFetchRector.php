<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Attribute;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @changelog https://github.com/doctrine/dbal/blob/3.1.x/src/Types/Types.php
 *
 * @see \Rector\Tests\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector\AttributeKeyToClassConstFetchRectorTest
 */
final class AttributeKeyToClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var AttributeKeyToClassConstFetch[]
     */
    private $attributeKeysToClassConstFetches = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace key value on specific attribute to class constant', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\Column;

class SomeClass
{
    #[Column(type: "string")]
    public $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping\Column;
use Doctrine\DBAL\Types\Types;

class SomeClass
{
    #[Column(type: Types::STRING)]
    public $name;
}
CODE_SAMPLE
, [new AttributeKeyToClassConstFetch('Doctrine\\ORM\\Mapping\\Column', 'type', 'Doctrine\\DBAL\\Types\\Types', ['string' => 'STRING'])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Attribute::class];
    }
    /**
     * @param Attribute $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->attributeKeysToClassConstFetches as $attributeKeyToClassConstFetch) {
            if (!$this->isName($node->name, $attributeKeyToClassConstFetch->getAttributeClass())) {
                continue;
            }
            foreach ($node->args as $arg) {
                $argName = $arg->name;
                if (!$argName instanceof Identifier) {
                    continue;
                }
                if (!$this->isName($argName, $attributeKeyToClassConstFetch->getAttributeKey())) {
                    continue;
                }
                $value = $this->valueResolver->getValue($arg->value);
                $constName = $attributeKeyToClassConstFetch->getValuesToConstantsMap()[$value] ?? null;
                if ($constName === null) {
                    continue;
                }
                $arg->value = $this->nodeFactory->createClassConstFetch($attributeKeyToClassConstFetch->getConstantClass(), $constName);
                return $node;
            }
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, AttributeKeyToClassConstFetch::class);
        $this->attributeKeysToClassConstFetches = $configuration;
    }
}
