<?php

declare (strict_types=1);
namespace Rector\Strict\Rector\Empty_;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as it creates unreadable code with messy isset()/comparison checks. Refactor the value to a single sole type instead, then the empty() check can be replaced by a clear comparison.
 */
final class DisallowedEmptyRuleFixerRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    /**
     * @api
     * @var string
     */
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change empty() check to explicit strict comparisons', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeEmptyArray
{
    public function run(array $items)
    {
        return empty($items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeEmptyArray
{
    public function run(array $items)
    {
        return $items === [];
    }
}
CODE_SAMPLE
, [self::TREAT_AS_NON_EMPTY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Empty_::class, BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as it creates unreadable code with messy checks; refactor the value to a sole type instead', self::class));
    }
}
