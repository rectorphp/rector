<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use Rector\Exception\ShouldNotHappenException;
use Rector\PHPUnit\Enum\ConsecutiveMethodName;
use Rector\PHPUnit\Enum\ConsecutiveVariable;
use Rector\PHPUnit\MethodCallRemover;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFinder\MethodCallNodeFinder;
use Rector\PHPUnit\PHPUnit100\NodeDecorator\WillReturnIfNodeDecorator;
use Rector\PHPUnit\PHPUnit100\NodeFactory\WillReturnCallbackFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\StmtsAwareInterface\WithConsecutiveRector\WithConsecutiveRectorTest
 */
final class WithConsecutiveRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPUnit\PHPUnit100\NodeFactory\WillReturnCallbackFactory
     */
    private $willReturnCallbackFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\MethodCallRemover
     */
    private $methodCallRemover;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFinder\MethodCallNodeFinder
     */
    private $methodCallNodeFinder;
    /**
     * @readonly
     * @var \Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface\ExpectsMethodCallDecorator
     */
    private $expectsMethodCallDecorator;
    /**
     * @readonly
     * @var \Rector\PHPUnit\PHPUnit100\NodeDecorator\WillReturnIfNodeDecorator
     */
    private $willReturnIfNodeDecorator;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, WillReturnCallbackFactory $willReturnCallbackFactory, MethodCallRemover $methodCallRemover, MethodCallNodeFinder $methodCallNodeFinder, \Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface\ExpectsMethodCallDecorator $expectsMethodCallDecorator, WillReturnIfNodeDecorator $willReturnIfNodeDecorator)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->willReturnCallbackFactory = $willReturnCallbackFactory;
        $this->methodCallRemover = $methodCallRemover;
        $this->methodCallNodeFinder = $methodCallNodeFinder;
        $this->expectsMethodCallDecorator = $expectsMethodCallDecorator;
        $this->willReturnIfNodeDecorator = $willReturnIfNodeDecorator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor deprecated withConsecutive() to willReturnCallback() structure', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $this->personServiceMock->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                [1, 2],
                [3, 4],
            );
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function run()
    {
        $matcher = $this->exactly(2);

        $this->personServiceMock->expects($matcher)
            ->method('prepare')
            ->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    self::assertEquals([1, 2], $parameters);
                }

                if ($matcher->numberOfInvocations() === 2) {
                    self::assertEquals([3, 4], $parameters),
                };
            });
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node)
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $withConsecutiveMethodCall = $this->methodCallNodeFinder->findByName($node, ConsecutiveMethodName::WITH_CONSECUTIVE);
        if (!$withConsecutiveMethodCall instanceof MethodCall) {
            return null;
        }
        if ($this->methodCallNodeFinder->hasByNames($node, ['willReturnMap', 'will'])) {
            return null;
        }
        $returnStmt = null;
        $willReturn = $this->methodCallNodeFinder->findByName($node, ConsecutiveMethodName::WILL_RETURN);
        if ($willReturn instanceof MethodCall) {
            $this->methodCallRemover->removeMethodCall($node, ConsecutiveMethodName::WILL_RETURN);
            $expr = $this->getFirstArgValue($willReturn);
            $returnStmt = new Return_($expr);
        }
        $willReturnSelf = $this->methodCallNodeFinder->findByName($node, ConsecutiveMethodName::WILL_RETURN_SELF);
        if ($willReturnSelf instanceof MethodCall) {
            $this->methodCallRemover->removeMethodCall($node, ConsecutiveMethodName::WILL_RETURN_SELF);
            $returnStmt = $this->createWillReturnSelfStmts($willReturnSelf);
        }
        $willReturnArgument = $this->methodCallNodeFinder->findByName($node, ConsecutiveMethodName::WILL_RETURN_ARGUMENT);
        if ($willReturnArgument instanceof MethodCall) {
            $this->methodCallRemover->removeMethodCall($node, ConsecutiveMethodName::WILL_RETURN_ARGUMENT);
            $returnStmt = $this->createWillReturnArgument($willReturnArgument);
        }
        $willReturnOnConsecutiveMethodCall = $this->methodCallNodeFinder->findByName($node, ConsecutiveMethodName::WILL_RETURN_ON_CONSECUTIVE_CALLS);
        if ($willReturnOnConsecutiveMethodCall instanceof MethodCall) {
            $this->methodCallRemover->removeMethodCall($node, ConsecutiveMethodName::WILL_RETURN_ON_CONSECUTIVE_CALLS);
        }
        $willThrowException = $this->methodCallNodeFinder->findByName($node, ConsecutiveMethodName::WILL_THROW_EXCEPTION);
        if ($willThrowException instanceof MethodCall) {
            $this->methodCallRemover->removeMethodCall($node, ConsecutiveMethodName::WILL_THROW_EXCEPTION);
            $expr = $this->getFirstArgValue($willThrowException);
            $returnStmt = new Throw_($expr);
        }
        $willReturnReferenceArgument = $this->methodCallNodeFinder->findByName($node, ConsecutiveMethodName::WILL_RETURN_REFERENCE);
        $referenceVariable = null;
        if ($willReturnReferenceArgument instanceof MethodCall) {
            $this->methodCallRemover->removeMethodCall($node, ConsecutiveMethodName::WILL_RETURN_REFERENCE);
            $expr = $this->getFirstArgValue($willReturnReferenceArgument);
            $returnStmt = new Return_($expr);
            // returns passed args
            $referenceVariable = new Variable('parameters');
        }
        $expectsCall = $this->expectsMethodCallDecorator->decorate($node);
        if (!$expectsCall instanceof MethodCall && !$expectsCall instanceof StaticCall) {
            // fallback to default by case count
            $lNumber = new LNumber(\count($withConsecutiveMethodCall->args));
            $expectsCall = new MethodCall(new Variable('this'), new Identifier('exactly'), [new Arg($lNumber)]);
        }
        // 2. does willReturnCallback() exist? just merge them together
        $existingWillReturnCallback = $this->methodCallNodeFinder->findByName($node, ConsecutiveMethodName::WILL_RETURN_CALLBACK);
        if ($existingWillReturnCallback instanceof MethodCall) {
            return $this->refactorWithExistingWillReturnCallback($existingWillReturnCallback, $withConsecutiveMethodCall, $node);
        }
        // 3. rename and replace withConsecutive()
        return $this->refactorToWillReturnCallback($withConsecutiveMethodCall, $returnStmt, $referenceVariable, $expectsCall, $node, $willReturnOnConsecutiveMethodCall);
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable|null $referenceVariable
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $expectsCall
     */
    private function refactorToWillReturnCallback(MethodCall $withConsecutiveMethodCall, ?Stmt $returnStmt, $referenceVariable, $expectsCall, Expression $expression, ?MethodCall $willReturnOnConsecutiveMethodCall) : array
    {
        $closure = $this->willReturnCallbackFactory->createClosure($withConsecutiveMethodCall, $returnStmt, $referenceVariable);
        $withConsecutiveMethodCall->name = new Identifier(ConsecutiveMethodName::WILL_RETURN_CALLBACK);
        $withConsecutiveMethodCall->args = [new Arg($closure)];
        $matcherVariable = new Variable(ConsecutiveVariable::MATCHER);
        $matcherAssign = new Assign($matcherVariable, $expectsCall);
        $this->willReturnIfNodeDecorator->decorate($closure, $willReturnOnConsecutiveMethodCall);
        return [new Expression($matcherAssign), $expression];
    }
    private function refactorWithExistingWillReturnCallback(MethodCall $existingWillReturnCallback, MethodCall $withConsecutiveMethodCall, Expression $expression) : Expression
    {
        $callbackArg = $existingWillReturnCallback->getArgs()[0];
        if (!$callbackArg->value instanceof Closure) {
            throw new ShouldNotHappenException();
        }
        $callbackClosure = $callbackArg->value;
        $callbackClosure->params[] = new Param(new Variable(ConsecutiveVariable::PARAMETERS));
        $parametersMatch = $this->willReturnCallbackFactory->createParametersMatch($withConsecutiveMethodCall);
        $callbackClosure->stmts = \array_merge($parametersMatch, $callbackClosure->stmts);
        $this->methodCallRemover->removeMethodCall($expression, ConsecutiveMethodName::WITH_CONSECUTIVE);
        return $expression;
    }
    private function createWillReturnSelfStmts(MethodCall $willReturnSelfMethodCall) : Return_
    {
        $selfVariable = $willReturnSelfMethodCall;
        while (\true) {
            if (!$selfVariable instanceof MethodCall) {
                break;
            }
            $selfVariable = $selfVariable->var;
        }
        return new Return_($selfVariable);
    }
    private function createWillReturnArgument(MethodCall $willReturnArgumentMethodCall) : Return_
    {
        $parametersVariable = new Variable(ConsecutiveVariable::PARAMETERS);
        $expr = $this->getFirstArgValue($willReturnArgumentMethodCall);
        return new Return_(new ArrayDimFetch($parametersVariable, $expr));
    }
    private function getFirstArgValue(MethodCall $methodCall) : Expr
    {
        $firstArg = $methodCall->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            throw new ShouldNotHappenException();
        }
        return $firstArg->value;
    }
}
