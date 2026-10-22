<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Transform\ValueObject\ConstFetchToClassConstFetch;
/**
 * @deprecated This rule is deprecated, as too niche and never used. A global const name can be reused across unrelated contexts and does not map cleanly to a single class constant. Use a custom rule scoped to your exact case instead.
 */
final class ConstFetchToClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change const fetch to class const fetch', [new ConfiguredCodeSample('$x = CONTEXT_COURSE', '$x = course::LEVEL', [new ConstFetchToClassConstFetch('CONTEXT_COURSE', 'course', 'LEVEL')])]);
    }
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }
    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?ClassConstFetch
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as too niche and never used; use a custom rule scoped to your exact case instead', self::class));
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
