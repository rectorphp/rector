<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\PHPUnit\CodeQuality\ValueObject\MatchAndReturnMatch;
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
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
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
            ->with(1, $parameters);
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
        $matchAndReturnMatch = $this->matchClosureSingleStmtMatch($firstArg->value);
        if (!$matchAndReturnMatch instanceof MatchAndReturnMatch) {
            return null;
        }
        $matchArmBody = $this->matchSingleMatchArmBodyWithConditionOne($matchAndReturnMatch->getConsecutiveMatch());
        if (!$matchArmBody instanceof Expr) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($matchArmBody, ['assertSame', 'assertEquals'])) {
            return null;
        }
        // we look for $this->assertSame(...)
        $expectedExpr = $matchAndReturnMatch->getConsecutiveMatchExpr();
        if ($expectedExpr instanceof Array_) {
            $args = $this->nodeFactory->createArgs($expectedExpr->items);
        } else {
            $args = [new Arg($expectedExpr)];
        }
        $node->name = new Identifier('with');
        $node->args = $args;
        // remove the returnCallback if present
        if ($matchAndReturnMatch->getWillReturnMatch() instanceof Match_) {
            return new MethodCall($node, new Identifier('willReturn'), [new Arg($matchAndReturnMatch->getWillReturnMatchExpr())]);
        }
        return $node;
    }
    private function matchClosureSingleStmtMatch(Expr $expr) : ?MatchAndReturnMatch
    {
        if (!$expr instanceof Closure) {
            return null;
        }
        // we need match or match + return match
        if (\count($expr->stmts) < 1 || \count($expr->stmts) > 2) {
            return null;
        }
        $onlyStmts = $expr->stmts[0];
        if (!$onlyStmts instanceof Expression) {
            return null;
        }
        if (!$onlyStmts->expr instanceof Match_) {
            return null;
        }
        $returnMatch = null;
        if (\count($expr->stmts) === 2) {
            $secondStmt = $expr->stmts[1];
            if (!$secondStmt instanceof Return_) {
                return null;
            }
            if (!$secondStmt->expr instanceof Match_) {
                return null;
            }
            $returnMatch = $secondStmt->expr;
        }
        return new MatchAndReturnMatch($onlyStmts->expr, $returnMatch);
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
        if (!$expr instanceof Int_) {
            return \false;
        }
        return $expr->value === 1;
    }
}
