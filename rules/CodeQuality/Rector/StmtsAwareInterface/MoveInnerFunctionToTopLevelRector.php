<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\StmtsAwareInterface;

use PhpParser\Node;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as it handles a niche inner-function case that is not part of the PHP upgrade path. Use a custom rule instead.
 */
final class MoveInnerFunctionToTopLevelRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move an inner named function to the top level, as inner named functions are not supported by PHPStan', [new CodeSample(<<<'CODE_SAMPLE'
function outer(): void
{
    function inner(): void
    {
        echo 'hello';
    }

    inner();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function inner(): void
{
    echo 'hello';
}

function outer(): void
{
    inner();
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as it handles a niche inner-function case that is not part of the PHP upgrade path', self::class));
    }
}
