<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Attribute;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @api used in rector-doctrine
 * @see \Rector\Tests\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector\AttributeKeyToClassConstFetchRectorTest
 */
final class AttributeKeyToClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var AttributeKeyToClassConstFetch[]
     */
    private array $attributeKeysToClassConstFetches = [];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
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
        return [Class_::class, Property::class, Param::class, ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class, Interface_::class];
    }
    /**
     * @param Class_|Property|Param|ClassMethod|Function_|Closure|ArrowFunction|Interface_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->attrGroups === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($this->attributeKeysToClassConstFetches as $attributeKeyToClassConstFetch) {
            foreach ($node->attrGroups as $attrGroup) {
                if ($this->processToClassConstFetch($attrGroup, $attributeKeyToClassConstFetch)) {
                    $hasChanged = \true;
                }
            }
        }
        if ($hasChanged) {
            return $node;
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
    private function processToClassConstFetch(AttributeGroup $attributeGroup, AttributeKeyToClassConstFetch $attributeKeyToClassConstFetch) : bool
    {
        $hasChanged = \false;
        foreach ($attributeGroup->attrs as $attribute) {
            if (!$this->isName($attribute->name, $attributeKeyToClassConstFetch->getAttributeClass())) {
                continue;
            }
            foreach ($attribute->args as $arg) {
                $argName = $arg->name;
                if (!$argName instanceof Identifier) {
                    continue;
                }
                if (!$this->isName($argName, $attributeKeyToClassConstFetch->getAttributeKey())) {
                    continue;
                }
                if ($this->processArg($arg, $attributeKeyToClassConstFetch)) {
                    $hasChanged = \true;
                }
            }
        }
        return $hasChanged;
    }
    private function processArg(Arg $arg, AttributeKeyToClassConstFetch $attributeKeyToClassConstFetch) : bool
    {
        $value = $this->valueResolver->getValue($arg->value);
        $constName = $attributeKeyToClassConstFetch->getValuesToConstantsMap()[$value] ?? null;
        if ($constName === null) {
            return \false;
        }
        $classConstFetch = $this->nodeFactory->createClassConstFetch($attributeKeyToClassConstFetch->getConstantClass(), $constName);
        if ($arg->value instanceof ClassConstFetch && $this->getName($arg->value) === $this->getName($classConstFetch)) {
            return \false;
        }
        $arg->value = $classConstFetch;
        return \true;
    }
}
