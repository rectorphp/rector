<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated. Use ExplicitNullableObjectInstanceRector instead, which handles nullable object checks across all condition idioms.
 */
final class FlipTypeControlToUseExclusiveTypeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Flip type control from null compare to use exclusive instanceof object', [new CodeSample(<<<'CODE_SAMPLE'
function process(?DateTime $dateTime)
{
    if ($dateTime === null) {
        return;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function process(?DateTime $dateTime)
{
    if (! $dateTime instanceof DateTime) {
        return;
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
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, use ExplicitNullableObjectInstanceRector instead', self::class));
    }
}
