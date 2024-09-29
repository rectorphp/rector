<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\NarrowSingleWillReturnCallbackRector\NarrowSingleWillReturnCallbackRectorTest
 */
final class NarrowSingleWillReturnCallbackRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Narrow single-value match willReturnCallback() to with() and willReturn() call', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $matcher = $this->exactly(1);

        $this->personServiceMock->expects($matcher)
            ->willReturnCallback(function (...$parameters) use ($matcher) {
                match ($matcher->getInvocationCount()) {
                    1 => $this->assertSame([1], $parameters),
                };
            });
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $matcher = $this->exactly(1);

        $this->personServiceMock->expects($matcher)
            ->with([1], $parameters);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<MethodCall>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Expr\MethodCall
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
        if (\count($node->getArgs()) !== 1) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        $match = $this->matchClosureSingleStmtMatch($firstArg->value);
        if (!$match instanceof Match_) {
            return null;
        }
        $matchArmBody = $this->matchSingleMatchArmBodyWithConditionOne($match);
        if (!$matchArmBody instanceof MethodCall) {
            return null;
        }
        // we look for $this->assertSame(...)
        if (!$this->isLocalMethodCall($matchArmBody, 'assertSame')) {
            return null;
        }
        $expectedArg = $matchArmBody->getArgs()[0];
        $node->name = new Identifier('with');
        $node->args = [new Arg($expectedArg->value)];
        return $node;
    }
    private function matchClosureSingleStmtMatch(Expr $expr) : ?\PhpParser\Node\Expr\Match_
    {
        if (!$expr instanceof Closure) {
            return null;
        }
        // handle easy path of single stmt first
        if (\count($expr->stmts) !== 1) {
            return null;
        }
        $onlyStmts = $expr->stmts[0];
        if (!$onlyStmts instanceof Expression) {
            return null;
        }
        if (!$onlyStmts->expr instanceof Match_) {
            return null;
        }
        return $onlyStmts->expr;
    }
    private function isLocalMethodCall(Expr $expr, string $methodName) : bool
    {
        if (!$expr instanceof MethodCall) {
            return \false;
        }
        if (!$this->isName($expr->var, 'this')) {
            return \false;
        }
        return $this->isName($expr->name, $methodName);
    }
    private function matchSingleMatchArmBodyWithConditionOne(Match_ $match) : ?Expr
    {
        // has more options
        if (\count($match->arms) !== 1) {
            return null;
        }
        $onlyArm = $match->arms[0];
        if ($onlyArm->conds === null || \count($onlyArm->conds) !== 1) {
            return null;
        }
        $onlyArmCond = $onlyArm->conds[0];
        if (!$this->isNumberOne($onlyArmCond)) {
            return null;
        }
        return $onlyArm->body;
    }
    private function isNumberOne(Expr $expr) : bool
    {
        if (!$expr instanceof LNumber) {
            return \false;
        }
        return $expr->value === 1;
    }
}
