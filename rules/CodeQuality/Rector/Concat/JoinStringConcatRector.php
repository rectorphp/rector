<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Concat;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as depends on context and personal preference, hard to generalize. Handle it manually or via a custom rule instead.
 */
final class JoinStringConcatRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Joins concat of 2 strings, unless the length is too long', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi' . ' Tom';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $name = 'Hi Tom';
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
        return [Concat::class];
    }
    /**
     * @param Concat $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated as depends on context and personal preference, hard to generalize. Handle it manually or via a custom rule instead', self::class));
    }
}
