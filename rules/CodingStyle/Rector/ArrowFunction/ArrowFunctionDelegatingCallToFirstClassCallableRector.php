<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\VariadicPlaceholder;
use Rector\CodingStyle\Guard\ArrowFunctionAndClosureFirstClassCallableGuard;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ArrowFunction\ArrowFunctionDelegatingCallToFirstClassCallableRector\ArrowFunctionDelegatingCallToFirstClassCallableRectorTest
 */
final class ArrowFunctionDelegatingCallToFirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ArrowFunctionAndClosureFirstClassCallableGuard $arrowFunctionAndCLosureFirstClassCallableGuard;
    public function __construct(ArrowFunctionAndClosureFirstClassCallableGuard $arrowFunctionAndCLosureFirstClassCallableGuard)
    {
        $this->arrowFunctionAndCLosureFirstClassCallableGuard = $arrowFunctionAndCLosureFirstClassCallableGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert nested arrow function call to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
fn ($parameter) => Call::to($parameter);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Call::to(...);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ArrowFunction::class];
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Expr\CallLike
    {
        if (!$node->expr instanceof FuncCall && !$node->expr instanceof MethodCall && !$node->expr instanceof StaticCall) {
            return null;
        }
        $callLike = $node->expr;
        // dynamic name? skip
        if ($callLike->name instanceof Expr) {
            return null;
        }
        if ($this->arrowFunctionAndCLosureFirstClassCallableGuard->shouldSkip($node, $callLike, ScopeFetcher::fetch($node))) {
            return null;
        }
        // turn into first class callable
        $callLike->args = [new VariadicPlaceholder()];
        return $callLike;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::FIRST_CLASS_CALLABLE_SYNTAX;
    }
}
