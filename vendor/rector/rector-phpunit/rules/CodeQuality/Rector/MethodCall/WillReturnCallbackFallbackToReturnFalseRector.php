<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\NeverType;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\WillReturnCallbackFallbackToReturnFalseRector\WillReturnCallbackFallbackToReturnFalseRectorTest
 */
final class WillReturnCallbackFallbackToReturnFalseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add a "return false" fallback to a willReturnCallback() closure that only returns booleans, but can fall through without a return', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $matcher = $this->exactly(2);

        $this->eventMock->expects($matcher)
            ->method('checkContext')
            ->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    return true;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    return false;
                }
            });
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $matcher = $this->exactly(2);

        $this->eventMock->expects($matcher)
            ->method('checkContext')
            ->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    return true;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    return false;
                }

                return false;
            });
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?MethodCall
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'willReturnCallback')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (count($node->getArgs()) !== 1) {
            return null;
        }
        $closure = $node->getArgs()[0]->value;
        if (!$closure instanceof Closure) {
            return null;
        }
        if ($closure->stmts === []) {
            return null;
        }
        // already ends with an explicit return, no fallback needed;
        // skip trailing comment-only Nop statements added by a trailing comment
        $stmts = array_filter($closure->stmts, static fn(Node $stmt): bool => !$stmt instanceof Nop);
        $lastStmt = end($stmts);
        if ($lastStmt instanceof Return_) {
            return null;
        }
        // closure cannot fall through: last statement throws or calls a never-returning method
        if ($lastStmt instanceof Expression && $this->isNeverReturningExpression($lastStmt->expr)) {
            return null;
        }
        if (!$this->hasOnlyBoolReturns($closure)) {
            return null;
        }
        $closure->stmts[] = new Return_($this->nodeFactory->createFalse());
        return $node;
    }
    private function hasOnlyBoolReturns(Closure $closure): bool
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfScoped($closure->stmts, [Return_::class]);
        if ($returns === []) {
            return \false;
        }
        foreach ($returns as $return) {
            if (!$return->expr instanceof ConstFetch) {
                return \false;
            }
            if (!$this->isNames($return->expr->name, ['true', 'false'])) {
                return \false;
            }
        }
        return \true;
    }
    private function isNeverReturningExpression(Expr $expr): bool
    {
        // cheap structural check first, before reflection-based type resolution
        if ($expr instanceof Throw_) {
            return \true;
        }
        return $this->getType($expr) instanceof NeverType;
    }
}
