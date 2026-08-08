<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Coalesce;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as risky. The "??" and "?:" operators are not interchangeable: "?:" also falls back on empty string, "0" and empty array. A regression must be fixed manually, so the rule is removed instead.
 *
 * @see https://github.com/rectorphp/rector/issues/9730
 */
final class CoalesceToTernaryRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace coalesce to ternary when left side is non nullable', [new CodeSample(<<<'CODE_SAMPLE'
function run(string $a)
{
	return $a ?? 'foo';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run(string $a)
{
	return $a ?: 'foo';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Coalesce::class];
    }
    /**
     * @param Coalesce $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as risky. The "??" and "?:" operators are not interchangeable and a regression has to be fixed manually', self::class));
    }
}
