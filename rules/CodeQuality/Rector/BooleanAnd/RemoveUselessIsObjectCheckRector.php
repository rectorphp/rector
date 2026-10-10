<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as it handles a niche is_object() with instanceof case that is not part of the PHP upgrade path.
 */
final class RemoveUselessIsObjectCheckRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove useless is_object() check on combine with instanceof check', [new CodeSample('is_object($obj) && $obj instanceof DateTime', '$obj instanceof DateTime')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanAnd::class];
    }
    /**
     * @param BooleanAnd $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as it handles a niche is_object() with instanceof case that is not part of the PHP upgrade path', self::class));
    }
}
