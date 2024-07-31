<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\StmtsAwareInterface;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeTraverser;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\StmtsAwareInterface\WithConsecutiveRector\WithConsecutiveRectorTest
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
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \PhpParser\BuilderFactory
     */
    private $builderFactory;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, BuilderFactory $builderFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->builderFactory = $builderFactory;
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
            ->willReturnCallback(function ($parameters) use ($matcher) {
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
        $withConsecutiveMethodCall = $this->findMethodCall($node, 'withConsecutive');
        if (!$withConsecutiveMethodCall instanceof MethodCall) {
            return null;
        }
        if ($this->hasWillReturnMapOrWill($node)) {
            return null;
        }
        $returnStmts = [];
        $willReturn = $this->findMethodCall($node, 'willReturn');
        if ($willReturn instanceof MethodCall) {
            $args = $willReturn->getArgs();
            if (\count($args) !== 1 || !$args[0] instanceof Arg) {
                return null;
            }
            $returnStmts = [new Return_($args[0]->value)];
        }
        $willReturnSelf = $this->findMethodCall($node, 'willReturnSelf');
        if ($willReturnSelf instanceof MethodCall) {
            if ($returnStmts !== []) {
                return null;
            }
            $selfVariable = $willReturnSelf;
            while (\true) {
                if (!$selfVariable instanceof MethodCall) {
                    break;
                }
                $selfVariable = $selfVariable->var;
            }
            $returnStmts = [new Return_($selfVariable)];
        }
        $willReturnArgument = $this->findMethodCall($node, 'willReturnArgument');
        if ($willReturnArgument instanceof MethodCall) {
            if ($returnStmts !== []) {
                return null;
            }
            $parametersVariable = new Variable('parameters');
            $args = $willReturnArgument->getArgs();
            if (\count($args) !== 1 || !$args[0] instanceof Arg) {
                return null;
            }
            $returnStmts = [new Return_(new ArrayDimFetch($parametersVariable, $args[0]->value))];
        }
        $willReturnOnConsecutiveCallsArgument = $this->findMethodCall($node, 'willReturnOnConsecutiveCalls');
        if ($willReturnOnConsecutiveCallsArgument instanceof MethodCall) {
            if ($returnStmts !== []) {
                return null;
            }
            $matcherVariable = new Variable('matcher');
            $numberOfInvocationsMethodCall = new MethodCall($matcherVariable, new Identifier('numberOfInvocations'));
            $matchArms = [];
            foreach ($willReturnOnConsecutiveCallsArgument->getArgs() as $key => $arg) {
                $matchArms[] = new MatchArm([new LNumber($key + 1)], $arg->value);
            }
            $returnStmts = [new Return_(new Match_($numberOfInvocationsMethodCall, $matchArms))];
        }
        $willReturnReferenceArgument = $this->findMethodCall($node, 'willReturnReference');
        $referenceVariable = null;
        if ($willReturnReferenceArgument instanceof MethodCall) {
            if ($returnStmts !== []) {
                return null;
            }
            $args = $willReturnReferenceArgument->args;
            if (\count($args) !== 1 || !$args[0] instanceof Arg) {
                return null;
            }
            $referenceVariable = $args[0]->value;
            if (!$referenceVariable instanceof Variable) {
                return null;
            }
            $returnStmts = [new Return_($referenceVariable)];
        }
        $willThrowException = $this->findMethodCall($node, 'willThrowException');
        if ($willThrowException instanceof MethodCall) {
            if ($returnStmts !== []) {
                return null;
            }
            $args = $willThrowException->getArgs();
            if (\count($args) !== 1 || !$args[0] instanceof Arg) {
                return null;
            }
            $returnStmts = [new Throw_($args[0]->value)];
        }
        /**
         * remove willReturn, willReturnArgument, willReturnOnConsecutiveCalls, willReturnReference
         * willReturnSelf and willThrowException
         */
        $this->removeWills($node);
        $expectsCall = $this->matchAndRefactorExpectsMethodCall($node);
        if (!$expectsCall instanceof MethodCall && !$expectsCall instanceof StaticCall) {
            return null;
        }
        // 2. rename and replace withConsecutive()
        $withConsecutiveMethodCall->name = new Identifier('willReturnCallback');
        $withConsecutiveMethodCall->args = [new Arg($this->createClosure($withConsecutiveMethodCall, $returnStmts, $referenceVariable))];
        $matcherAssign = new Assign(new Variable('matcher'), $expectsCall);
        return [new Expression($matcherAssign), $node];
    }
    public function provideMinPhpVersion() : int
    {
        /**
         * This rule just work for phpunit 10,
         * And as php 8.1 is the min version supported by phpunit 10, then we decided to let this version as minimum.
         *
         * You can see more detail in this issue: https://github.com/rectorphp/rector-phpunit/issues/272
         */
        return PhpVersion::PHP_81;
    }
    /**
     * @template T of Node
     * @param Node[] $nodes
     * @param class-string<T> $type
     * @return T[]
     */
    private function findInstancesOfScoped(array $nodes, string $type) : array
    {
        /** @var T[] $foundNodes */
        $foundNodes = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, static function (Node $subNode) use($type, &$foundNodes) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($subNode instanceof $type) {
                $foundNodes[] = $subNode;
                return null;
            }
            return null;
        });
        return $foundNodes;
    }
    /**
     * @param Node\Stmt[] $returnStmts
     */
    private function createClosure(MethodCall $expectsMethodCall, array $returnStmts, ?Variable $referenceVariable) : Closure
    {
        $closure = new Closure();
        $byRef = $referenceVariable instanceof Variable;
        $closure->byRef = $byRef;
        $matcherVariable = new Variable('matcher');
        $closure->uses[] = new ClosureUse($matcherVariable);
        $usedVariables = $this->resolveUniqueUsedVariables(\array_merge($expectsMethodCall->getArgs(), $this->resolveUniqueUsedVariables($returnStmts)));
        foreach ($usedVariables as $usedVariable) {
            $closureUse = new ClosureUse($usedVariable);
            if ($byRef && $this->getName($usedVariable) === $this->getName($referenceVariable)) {
                $closureUse->byRef = \true;
            }
            $closure->uses[] = $closureUse;
        }
        $parametersVariable = new Variable('parameters');
        $match = $this->createMatch($matcherVariable, $expectsMethodCall, $parametersVariable);
        $closure->params[] = new Param($parametersVariable);
        $closure->stmts = \array_merge([new Expression($match)], $returnStmts);
        return $closure;
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
            $firstArg = $node->getArgs()[0];
            if (!$firstArg->value instanceof MethodCall && !$firstArg->value instanceof StaticCall) {
                return null;
            }
            $exactlyCall = $firstArg->value;
            $node->args = [new Arg(new Variable('matcher'))];
            return $node;
        });
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
    private function createMatch(Variable $matcherVariable, MethodCall $expectsMethodCall, Variable $parameters) : Match_
    {
        $numberOfInvocationsMethodCall = new MethodCall($matcherVariable, new Identifier('numberOfInvocations'));
        $matchArms = [];
        foreach ($expectsMethodCall->getArgs() as $key => $arg) {
            $assertEquals = $this->builderFactory->staticCall('self', 'assertEquals', [$arg, $parameters]);
            $matchArms[] = new MatchArm([new LNumber($key + 1)], $assertEquals);
        }
        return new Match_($numberOfInvocationsMethodCall, $matchArms);
    }
    /**
     * @param Node[] $nodes
     * @return Variable[]
     */
    private function resolveUniqueUsedVariables(array $nodes) : array
    {
        /** @var Variable[] $usedVariables */
        $usedVariables = $this->findInstancesOfScoped($nodes, Variable::class);
        $uniqueUsedVariables = [];
        foreach ($usedVariables as $usedVariable) {
            if ($this->isNames($usedVariable, ['this', 'matcher', 'parameters'])) {
                continue;
            }
            $usedVariableName = $this->getName($usedVariable);
            $uniqueUsedVariables[$usedVariableName] = $usedVariable;
        }
        return $uniqueUsedVariables;
    }
    /**
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node $node
     */
    private function hasWillReturnMapOrWill($node) : bool
    {
        $nodesWithWillReturnMap = $this->betterNodeFinder->find($node, function (Node $node) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->isNames($node->name, ['willReturnMap', 'will']);
        });
        return $nodesWithWillReturnMap !== [];
    }
    /**
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node $expression
     */
    private function removeWills($expression) : void
    {
        $this->traverseNodesWithCallable($expression, function (Node $node) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isNames($node->name, ['willReturn', 'willReturnArgument', 'willReturnSelf', 'willReturnOnConsecutiveCalls', 'willReturnReference', 'willThrowException'])) {
                return null;
            }
            return $node->var;
        });
    }
}
