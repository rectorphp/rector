<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as renamed to SortCallLikeNamedArgsRector
 */
final class SortNamedParamRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Sort named arguments to match their order in a function or method call or class constructors', [new CodeSample(<<<'CODE_SAMPLE'
function run($foo = null, $bar = null, $baz = null) {}

run(bar: $bar, foo: $foo);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run($foo = null, $bar = null, $baz = null) {}

run(foo: $foo, bar: $bar);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, New_::class, FuncCall::class];
    }
    /**
     * @param MethodCall|StaticCall|New_|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('%s is deprecated as renamed to "%s".', self::class, \Rector\CodeQuality\Rector\FuncCall\SortCallLikeNamedArgsRector::class));
    }
}
