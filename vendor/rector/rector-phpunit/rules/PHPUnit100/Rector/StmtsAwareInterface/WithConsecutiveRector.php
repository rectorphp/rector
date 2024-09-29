<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeTraverser;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\Enum\ConsecutiveVariable;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory;
use Rector\PHPUnit\NodeFactory\WithConsecutiveMatchFactory;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\StmtsAwareInterface\WithConsecutiveRector\WithConsecutiveRectorTest
 */
final class WithConsecutiveRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\WithConsecutiveMatchFactory
     */
    private $withConsecutiveMatchFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory
     */
    private $matcherInvocationCountMethodCallNodeFactory;
    /**
     * @var string
     */
    private const WITH_CONSECUTIVE_METHOD = 'withConsecutive';
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder, WithConsecutiveMatchFactory $withConsecutiveMatchFactory, MatcherInvocationCountMethodCallNodeFactory $matcherInvocationCountMethodCallNodeFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->withConsecutiveMatchFactory = $withConsecutiveMatchFactory;
        $this->matcherInvocationCountMethodCallNodeFactory = $matcherInvocationCountMethodCallNodeFactory;
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
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertEquals([1, 2], $parameters),
                    2 => self::assertEquals([3, 4], $parameters),
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
        $withConsecutiveMethodCall = $this->findMethodCall($node, self::WITH_CONSECUTIVE_METHOD);
        if (!$withConsecutiveMethodCall instanceof MethodCall) {
            return null;
        }
        if ($this->hasWillReturnMapOrWill($node)) {
            return null;
        }
        $returnStmts = [];
        $willReturn = $this->findMethodCall($node, 'willReturn');
        if ($willReturn instanceof MethodCall) {
            $returnStmts[] = $this->createWillReturnStmt($willReturn);
        }
        $willReturnSelf = $this->findMethodCall($node, 'willReturnSelf');
        if ($willReturnSelf instanceof MethodCall) {
            $returnStmts[] = $this->createWillReturnSelfStmts($willReturnSelf);
        }
        $willReturnArgument = $this->findMethodCall($node, 'willReturnArgument');
        if ($willReturnArgument instanceof MethodCall) {
            $returnStmts[] = $this->createWillReturnArgument($willReturnArgument);
        }
        $willReturnOnConsecutiveCallsArgument = $this->findMethodCall($node, 'willReturnOnConsecutiveCalls');
        if ($willReturnOnConsecutiveCallsArgument instanceof MethodCall) {
            $returnStmts[] = $this->createReturnMatch($willReturnOnConsecutiveCallsArgument);
        }
        $willThrowException = $this->findMethodCall($node, 'willThrowException');
        if ($willThrowException instanceof MethodCall) {
            $returnStmts[] = $this->createWillThrowException($willThrowException);
        }
        $willReturnReferenceArgument = $this->findMethodCall($node, 'willReturnReference');
        $referenceVariable = null;
        if ($willReturnReferenceArgument instanceof MethodCall) {
            $returnStmts[] = $this->createWillReturn($willReturnReferenceArgument);
            // returns passed args
            $referenceVariable = new Variable('parameters');
        }
        $this->removeMethodCalls($node, ['willReturn', 'willReturnArgument', 'willReturnSelf', 'willReturnOnConsecutiveCalls', 'willReturnReference', 'willThrowException']);
        $expectsCall = $this->matchAndRefactorExpectsMethodCall($node);
        if (!$expectsCall instanceof MethodCall && !$expectsCall instanceof StaticCall) {
            // fallback to default by case count
            $lNumber = new LNumber(\count($withConsecutiveMethodCall->args));
            $expectsCall = new MethodCall(new Variable('this'), new Identifier('exactly'), [new Arg($lNumber)]);
        }
        // 2. does willReturnCallback() exist? just merge
        $existingWillReturnCallback = $this->findMethodCall($node, 'willReturnCallback');
        if ($existingWillReturnCallback instanceof MethodCall) {
            return $this->refactorWithExistingWillReturnCallback($existingWillReturnCallback, $withConsecutiveMethodCall, $node);
        }
        // 3. rename and replace withConsecutive()
        return $this->refactorToWillReturnCallback($withConsecutiveMethodCall, $returnStmts, $referenceVariable, $expectsCall, $node);
    }
    public function provideMinPhpVersion() : int
    {
        // This rule uses PHP 8.0 match
        return PhpVersion::PHP_80;
    }
    /**
     * Replace $this->expects(...)
     *
     * @param Expression<MethodCall> $expression
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    private function matchAndRefactorExpectsMethodCall(Expression $expression)
    {
        /** @var MethodCall|StaticCall|null $exactlyCall */
        $exactlyCall = null;
        $this->traverseNodesWithCallable($expression, function (Node $node) use(&$exactlyCall) : ?MethodCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'expects')) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            $firstArg = $node->getArgs()[0];
            if (!$firstArg->value instanceof MethodCall && !$firstArg->value instanceof StaticCall) {
                return null;
            }
            $exactlyCall = $firstArg->value;
            $node->args = [new Arg(new Variable(ConsecutiveVariable::MATCHER))];
            return $node;
        });
        // add expects() method
        if (!$exactlyCall instanceof Expr) {
            $this->traverseNodesWithCallable($expression, function (Node $node) : ?int {
                if (!$node instanceof MethodCall) {
                    return null;
                }
                if ($node->var instanceof MethodCall) {
                    return null;
                }
                $node->var = new MethodCall($node->var, 'expects', [new Arg(new Variable(ConsecutiveVariable::MATCHER))]);
                return NodeTraverser::STOP_TRAVERSAL;
            });
        }
        return $exactlyCall;
    }
    private function findMethodCall(Expression $expression, string $methodName) : ?MethodCall
    {
        if (!$expression->expr instanceof MethodCall) {
            return null;
        }
        /** @var MethodCall|null $methodCall */
        $methodCall = $this->betterNodeFinder->findFirst($expression->expr, function (Node $node) use($methodName) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->isName($node->name, $methodName);
        });
        return $methodCall;
    }
    private function hasWillReturnMapOrWill(Expression $expression) : bool
    {
        $nodesWithWillReturnMap = $this->betterNodeFinder->find($expression, function (Node $node) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->isNames($node->name, ['willReturnMap', 'will']);
        });
        return $nodesWithWillReturnMap !== [];
    }
    /**
     * @param Stmt[] $returnStmts
     * @return Stmt[]
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable|null $referenceVariable
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $expectsCall
     */
    private function refactorToWillReturnCallback(MethodCall $withConsecutiveMethodCall, array $returnStmts, $referenceVariable, $expectsCall, Expression $expression) : array
    {
        $closure = $this->withConsecutiveMatchFactory->createClosure($withConsecutiveMethodCall, $returnStmts, $referenceVariable);
        $withConsecutiveMethodCall->name = new Identifier('willReturnCallback');
        $withConsecutiveMethodCall->args = [new Arg($closure)];
        $matcherVariable = new Variable(ConsecutiveVariable::MATCHER);
        $matcherAssign = new Assign($matcherVariable, $expectsCall);
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
        $parametersMatch = $this->withConsecutiveMatchFactory->createParametersMatch($withConsecutiveMethodCall);
        if (\is_array($parametersMatch)) {
            $callbackClosure->stmts = \array_merge($parametersMatch, $callbackClosure->stmts);
        } else {
            $callbackClosure->stmts = \array_merge([new Expression($parametersMatch)], $callbackClosure->stmts);
        }
        $this->removeMethodCalls($expression, [self::WITH_CONSECUTIVE_METHOD]);
        return $expression;
    }
    /**
     * @param string[] $methodNames
     */
    private function removeMethodCalls(Expression $expression, array $methodNames) : void
    {
        $this->traverseNodesWithCallable($expression, function (Node $node) use($methodNames) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isNames($node->name, $methodNames)) {
                return null;
            }
            return $node->var;
        });
    }
    private function createWillReturnStmt(MethodCall $willReturnMethodCall) : Return_
    {
        $firstArg = $willReturnMethodCall->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            throw new ShouldNotHappenException();
        }
        return new Return_($firstArg->value);
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
    private function createWillThrowException(MethodCall $willThrowExceptionMethodCall) : Throw_
    {
        $firstArg = $willThrowExceptionMethodCall->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            throw new ShouldNotHappenException();
        }
        return new Throw_($firstArg->value);
    }
    private function createWillReturn(MethodCall $willReturnReferenceMethodCall) : Return_
    {
        $firstArg = $willReturnReferenceMethodCall->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            throw new ShouldNotHappenException();
        }
        $referenceVariable = $firstArg->value;
        if (!$referenceVariable instanceof Variable) {
            throw new ShouldNotHappenException();
        }
        return new Return_($referenceVariable);
    }
    private function createWillReturnArgument(MethodCall $willReturnArgumentMethodCall) : Return_
    {
        $parametersVariable = new Variable(ConsecutiveVariable::PARAMETERS);
        $firstArgs = $willReturnArgumentMethodCall->getArgs()[0];
        if (!$firstArgs instanceof Arg) {
            throw new ShouldNotHappenException();
        }
        return new Return_(new ArrayDimFetch($parametersVariable, $firstArgs->value));
    }
    private function createReturnMatch(MethodCall $willReturnOnConsecutiveCallsMethodCall) : Return_
    {
        $numberOfInvocationsMethodCall = $this->matcherInvocationCountMethodCallNodeFactory->create();
        $matchArms = [];
        foreach ($willReturnOnConsecutiveCallsMethodCall->getArgs() as $key => $arg) {
            $matchArms[] = new MatchArm([new LNumber($key + 1)], $arg->value);
        }
        return new Return_(new Match_($numberOfInvocationsMethodCall, $matchArms));
    }
}
