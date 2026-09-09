<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as it can turn valid code into broken code. The reverse assign order only matters for the same array variable, but the rule cannot reliably tell apart independent assigns, so wrapping with array_reverse() can change correct behavior.
 */
final class ListSwapArrayOrderRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('list() assigns variables in reverse order - relevant in array assign', [new CodeSample('list($a[], $a[]) = [1, 2];', 'list($a[], $a[]) = array_reverse([1, 2]);')]);
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
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as it can turn valid code into broken code', self::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::LIST_SWAP_ORDER;
    }
}
