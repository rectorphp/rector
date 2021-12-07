<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
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
    private const PROCESSABLE_WILL_STATEMENTS = ['will', 'willReturn', 'willReturnReference', 'willReturnMap', 'willReturnArgument', 'willReturnCallback', 'willReturnSelf', 'willThrowException'];
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @var \Rector\PHPUnit\NodeFactory\ConsecutiveAssertionFactory
     */
    private $consecutiveAssertionFactory;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer, \Rector\PHPUnit\NodeFactory\ConsecutiveAssertionFactory $consecutiveAssertionFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->consecutiveAssertionFactory = $consecutiveAssertionFactory;
    }
    /**
     * @param Expression[] $stmts
     */
    public function getExpectationsFromExpressions(array $stmts) : \Rector\PHPUnit\ValueObject\ExpectationMockCollection
    {
        $expectationMockCollection = new \Rector\PHPUnit\ValueObject\ExpectationMockCollection();
        foreach ($stmts as $stmt) {
            /** @var MethodCall $expr */
            $expr = $stmt->expr;
            $method = $this->getMethod($expr);
            if (!$this->testsNodeAnalyzer->isInPHPUnitMethodCallName($method, 'method')) {
                continue;
            }
            /** @var MethodCall $expects */
            $expects = $this->getExpects($method->var, $method);
            if (!$this->isValidExpectsCall($expects)) {
                continue;
            }
            $expectsArg = $expects->args[0];
            /** @var MethodCall $expectsValue */
            $expectsValue = $expectsArg->value;
            if (!$this->isValidAtCall($expectsValue)) {
                continue;
            }
            $atArg = $expectsValue->args[0];
            $atValue = $atArg->value;
            if (!$atValue instanceof \PhpParser\Node\Scalar\LNumber) {
                continue;
            }
            if (!$expects->var instanceof \PhpParser\Node\Expr\Variable && !$expects->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
                continue;
            }
            $expectationMockCollection->add(new \Rector\PHPUnit\ValueObject\ExpectationMock($expects->var, $method->args, $atValue->value, $this->getWill($expr), $this->getWithArgs($method->var), $stmt));
        }
        return $expectationMockCollection;
    }
    public function isValidExpectsCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->testsNodeAnalyzer->isInPHPUnitMethodCallName($methodCall, 'expects')) {
            return \false;
        }
        if (\count($methodCall->args) !== 1) {
            return \false;
        }
        return \true;
    }
    public function isValidAtCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->testsNodeAnalyzer->isInPHPUnitMethodCallName($methodCall, 'at')) {
            return \false;
        }
        if (\count($methodCall->args) !== 1) {
            return \false;
        }
        return \true;
    }
    private function getMethod(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\MethodCall
    {
        if ($this->testsNodeAnalyzer->isPHPUnitMethodCallNames($methodCall, self::PROCESSABLE_WILL_STATEMENTS) && $methodCall->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return $methodCall->var;
        }
        return $methodCall;
    }
    private function getWill(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Expr
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($methodCall, self::PROCESSABLE_WILL_STATEMENTS)) {
            return null;
        }
        return $this->consecutiveAssertionFactory->createWillReturn($methodCall);
    }
    private function getExpects(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr
    {
        if (!$expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return $methodCall;
        }
        if (!$this->testsNodeAnalyzer->isInPHPUnitMethodCallName($expr, 'with')) {
            return $methodCall->var;
        }
        return $expr->var;
    }
    /**
     * @return array<int, Expr|null>
     */
    private function getWithArgs(\PhpParser\Node\Expr $expr) : array
    {
        if (!$this->testsNodeAnalyzer->isInPHPUnitMethodCallName($expr, 'with')) {
            return [null];
        }
        if (!$expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return [null];
        }
        return \array_map(static function (\PhpParser\Node\Arg $arg) : Expr {
            return $arg->value;
        }, $expr->args);
    }
}
