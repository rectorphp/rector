<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as match(true) with nested conditions is often less readable and more confusing than the original ternary. Refactor to an explicit intent instead, e.g. early returns or a named method.
 */
final class NestedTernaryToMatchRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert nested ternary expressions to match(true) statements', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getValue($input)
    {
        return $input > 100 ? 'more than 100' : ($input > 5 ? 'more than 5' : 'less');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getValue($input)
    {
        return match (true) {
            $input > 100 => 'more than 100',
            $input > 5 => 'more than 5',
            default => 'less',
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
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Assign
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as match(true) with nested conditions is often less readable than the original ternary', self::class));
    }
}
