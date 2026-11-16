<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Attribute;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;
/**
 * @deprecated This rule is deprecated, as only used for 2 cases now covered by dedicated rules: ColumnTypeToTypesConstantRector in rector-doctrine and EmailModeToConstantRector in rector-symfony. Write a custom rule for the exact project case instead.
 */
final class AttributeKeyToClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
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
, [new AttributeKeyToClassConstFetch('Doctrine\ORM\Mapping\Column', 'type', 'Doctrine\DBAL\Types\Types', ['string' => 'STRING'])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Property::class, Param::class, ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class, Interface_::class];
    }
    /**
     * @param Class_|Property|Param|ClassMethod|Function_|Closure|ArrowFunction|Interface_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as only used for 2 cases now covered by dedicated rules; write a custom rule for the exact project case instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
