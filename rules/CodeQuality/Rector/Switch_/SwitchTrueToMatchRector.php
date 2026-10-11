<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Switch_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as both `switch (true)` and `match (true)` are hacky constructions; a constant list or explicit mapping is clearer and the change needs a human check.
 */
final class SwitchTrueToMatchRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `switch (true)` of returning cases to `match (true)` expression', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        switch (true) {
            case $value === 0:
                return 'no';
            case $value === 1:
                return 'yes';
            default:
                return 'maybe';
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return match (true) {
            $value === 0 => 'no',
            $value === 1 => 'yes',
            default => 'maybe',
        };
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as both switch (true) and match (true) are hacky constructions that need a human check', self::class));
    }
}
