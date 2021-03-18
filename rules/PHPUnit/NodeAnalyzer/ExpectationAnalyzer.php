<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\NodeFactory\ConsecutiveAssertionFactory;
use Rector\PHPUnit\ValueObject\ExpectationMock;
use Rector\PHPUnit\ValueObject\ExpectationMockCollection;

final class ExpectationAnalyzer
{
    /**
     * @var string[]
     */
    private const PROCESSABLE_WILL_STATEMENTS = [
        'will',
        'willReturn',
        'willReturnReference',
        'willReturnMap',
        'willReturnArgument',
        'willReturnCallback',
        'willReturnSelf',
        'willThrowException',
    ];

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    /**
     * @var ConsecutiveAssertionFactory
     */
    private $consecutiveAssertionFactory;

    public function __construct(
        TestsNodeAnalyzer $testsNodeAnalyzer,
        ConsecutiveAssertionFactory $consecutiveAssertionFactory
    ) {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->consecutiveAssertionFactory = $consecutiveAssertionFactory;
    }

    /**
     * @param Expression[] $stmts
     */
    public function getExpectationsFromExpressions(array $stmts): ExpectationMockCollection
    {
        $expectationMockCollection = new ExpectationMockCollection();
        foreach ($stmts as $stmt) {
            /** @var MethodCall $expr */
            $expr = $stmt->expr;
            $method = $this->getMethod($expr);
            if (! $this->testsNodeAnalyzer->isPHPUnitMethodName($method, 'method')) {
                continue;
            }

            /** @var MethodCall $expects */
            $expects = $this->getExpects($method->var, $method);
            if (! $this->isValidExpectsCall($expects)) {
                continue;
            }

            $expectsArg = $expects->args[0];
            /** @var MethodCall $expectsValue */
            $expectsValue = $expectsArg->value;
            if (! $this->isValidAtCall($expectsValue)) {
                continue;
            }

            $atArg = $expectsValue->args[0];
            $atValue = $atArg->value;
            if (! $atValue instanceof LNumber) {
                continue;
            }
            if (! $expects->var instanceof Variable) {
                continue;
            }

            $expectationMockCollection->add(
                new ExpectationMock(
                    $expects->var,
                    $method->args,
                    $atValue->value,
                    $this->getWill($expr),
                    $this->getWithArgs($method->var),
                    $stmt
                )
            );
        }

        return $expectationMockCollection;
    }

    public function isValidExpectsCall(MethodCall $methodCall): bool
    {
        if (! $this->testsNodeAnalyzer->isPHPUnitMethodName($methodCall, 'expects')) {
            return false;
        }

        if (count($methodCall->args) !== 1) {
            return false;
        }

        return true;
    }

    public function isValidAtCall(MethodCall $methodCall): bool
    {
        if (! $this->testsNodeAnalyzer->isPHPUnitMethodName($methodCall, 'at')) {
            return false;
        }

        if (count($methodCall->args) !== 1) {
            return false;
        }

        return true;
    }

    private function getMethod(MethodCall $methodCall): MethodCall
    {
        if ($this->testsNodeAnalyzer->isPHPUnitMethodNames(
            $methodCall,
            self::PROCESSABLE_WILL_STATEMENTS
        ) && $methodCall->var instanceof MethodCall) {
            return $methodCall->var;
        }

        return $methodCall;
    }

    private function getWill(MethodCall $methodCall): ?Expr
    {
        if (! $this->testsNodeAnalyzer->isPHPUnitMethodNames($methodCall, self::PROCESSABLE_WILL_STATEMENTS)) {
            return null;
        }

        return $this->consecutiveAssertionFactory->createWillReturn($methodCall);
    }

    private function getExpects(Expr $expr, MethodCall $methodCall): Expr
    {
        if (! $this->testsNodeAnalyzer->isPHPUnitMethodName($expr, 'with')) {
            return $methodCall->var;
        }
        if (! $expr instanceof MethodCall) {
            return $methodCall->var;
        }
        return $expr->var;
    }

    /**
     * @return array<int, Expr|null>
     */
    private function getWithArgs(Expr $expr): array
    {
        if (! $this->testsNodeAnalyzer->isPHPUnitMethodName($expr, 'with')) {
            return [null];
        }
        if (! $expr instanceof MethodCall) {
            return [null];
        }
        return array_map(static function (Arg $arg): Expr {
            return $arg->value;
        }, $expr->args);
    }
}
