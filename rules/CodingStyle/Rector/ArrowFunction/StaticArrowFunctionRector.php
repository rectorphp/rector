<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as noisy change with little value. Use manually or custom rule where needed instead.
 */
final class StaticArrowFunctionRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes ArrowFunction to be static when possible', [new CodeSample(<<<'CODE_SAMPLE'
fn (): string => 'test';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
static fn (): string => 'test';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ArrowFunction::class];
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated as noisy change with little value. Use manually or custom rule where needed instead', self::class));
    }
}
