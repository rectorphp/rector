<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use Rector\CodingStyle\Rector\ArrowFunction\ArrowFunctionDelegatingCallToFirstClassCallableRector;
use Rector\CodingStyle\Rector\Closure\ClosureDelegatingCallToFirstClassCallableRector;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule was split into
 * @see ClosureDelegatingCallToFirstClassCallableRector and
 * @see ArrowFunctionDelegatingCallToFirstClassCallableRector
 */
final class FunctionLikeToFirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Converts arrow function and closures to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
function ($parameter) {
    return Call::to($parameter);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Call::to(...);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ArrowFunction::class, Closure::class];
    }
    /**
     * @param ArrowFunction|Closure $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Expr\CallLike
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated. It was split into "%s" and "%s" rules.', self::class, ClosureDelegatingCallToFirstClassCallableRector::class, ArrowFunctionDelegatingCallToFirstClassCallableRector::class));
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::FIRST_CLASS_CALLABLE_SYNTAX;
    }
}
