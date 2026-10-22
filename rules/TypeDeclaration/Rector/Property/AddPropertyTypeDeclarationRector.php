<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\StringType;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\ValueObject\AddPropertyTypeDeclaration;
/**
 * @deprecated This rule is deprecated, as too niche and never used, not part of any set. Write a custom rule for the exact project case instead.
 */
final class AddPropertyTypeDeclarationRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add type to property by added rules, mostly public/property by parent type', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass extends ParentClass
{
    public $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass extends ParentClass
{
    public string $name;
}
CODE_SAMPLE
, [new AddPropertyTypeDeclaration('ParentClass', 'name', new StringType())])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as too niche and part of no set; write a custom rule for the exact project case instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
