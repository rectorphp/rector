<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ArrayDimFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Stmt\Unset_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as too niche and never used. It can also create invalid calls on objects without the target methods. Use a specific rule instead.
 */
final class ArrayDimFetchToMethodCallRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change array dim fetch to method call', [new CodeSample(<<<'CODE_SAMPLE'
$object['key'];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$object->get('key');
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Assign::class, Isset_::class, Unset_::class, ArrayDimFetch::class];
    }
    /**
     * @param Assign|Isset_|Unset_|ArrayDimFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as too niche and never used. Use a specific rule instead', self::class));
    }
}
