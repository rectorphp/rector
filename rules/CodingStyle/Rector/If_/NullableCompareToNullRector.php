<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as comparing a nullable object to null is ambiguous and weak. Use an "instanceof" check instead, e.g. the instanceof rule set.
 */
final class NullableCompareToNullRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes negate of empty comparison of nullable value to explicit === or !== compare', [new CodeSample(<<<'CODE_SAMPLE'
/** @var stdClass|null $value */
if ($value) {
}

if (!$value) {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var stdClass|null $value */
if ($value !== null) {
}

if ($value === null) {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as null compare is ambiguous and weak; use an "instanceof" check instead', self::class));
    }
}
