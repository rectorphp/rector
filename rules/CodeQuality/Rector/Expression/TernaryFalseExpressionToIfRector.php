<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as too niche for wide application.
 */
final class TernaryFalseExpressionToIfRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change ternary with false to if and explicit call', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($value, $someMethod)
    {
        $value ? $someMethod->call($value) : false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($value, $someMethod)
    {
        if ($value) {
            $someMethod->call($value);
        }
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as too niche for wide application', self::class));
    }
}
