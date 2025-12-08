<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\CodingStyle\Guard\ArrowFunctionAndClosureFirstClassCallableGuard;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Closure\ClosureDelegatingCallToFirstClassCallableRector\ClosureDelegatingCallToFirstClassCallableRectorTest
 */
final class ClosureDelegatingCallToFirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ArrowFunctionAndClosureFirstClassCallableGuard $arrowFunctionAndClosureFirstClassCallableGuard;
    public function __construct(ArrowFunctionAndClosureFirstClassCallableGuard $arrowFunctionAndClosureFirstClassCallableGuard)
    {
        $this->arrowFunctionAndClosureFirstClassCallableGuard = $arrowFunctionAndClosureFirstClassCallableGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert closure with sole nested call to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
function ($parameter) {
    return AnotherClass::someMethod($parameter);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
AnotherClass::someMethod(...);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     * @return null|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    public function refactor(Node $node)
    {
        // must have exactly 1 stmt with Return_
        if (count($node->stmts) !== 1 || !$node->stmts[0] instanceof Return_) {
            return null;
        }
        $callLike = $node->stmts[0]->expr;
        if (!$callLike instanceof FuncCall && !$callLike instanceof MethodCall && !$callLike instanceof StaticCall) {
            return null;
        }
        // dynamic name? skip
        if ($callLike->name instanceof Expr) {
            return null;
        }
        if ($this->arrowFunctionAndClosureFirstClassCallableGuard->shouldSkip($node, $callLike, ScopeFetcher::fetch($node))) {
            return null;
        }
        $callLike->args = [new VariadicPlaceholder()];
        return $callLike;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::FIRST_CLASS_CALLABLE_SYNTAX;
    }
}
