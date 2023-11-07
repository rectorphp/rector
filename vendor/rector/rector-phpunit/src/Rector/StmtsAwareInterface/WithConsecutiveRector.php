<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, BetterNodeFinder $betterNodeFinder, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
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
            ->willReturnCallback(function () use ($matcher) {
                return match ($matcher->numberOfInvocations()) {
                    1 => [1, 2],
                    2 => [3, 4]
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
        $withConsecutiveMethodCall = $this->findWithConsecutiveMethodCall($node);
        if (!$withConsecutiveMethodCall instanceof MethodCall) {
            return null;
        }
        $expectsMethodCall = $this->matchAndRefactorExpectsMethodCall($node);
        if (!$expectsMethodCall instanceof MethodCall) {
            return null;
        }
        // 2. rename and replace withConsecutive()
        $withConsecutiveMethodCall->name = new Identifier('willReturnCallback');
        $withConsecutiveMethodCall->args = [new Arg($this->createClosure($withConsecutiveMethodCall))];
        $matcherAssign = new Assign(new Variable('matcher'), $expectsMethodCall);
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
     * @param Node|Node[] $node
     * @param class-string<T> $type
     * @return T[]
     */
    public function findInstancesOfScoped($node, string $type) : array
    {
        /** @var T[] $foundNodes */
        $foundNodes = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, static function (Node $subNode) use($type, &$foundNodes) : ?int {
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
    private function createClosure(MethodCall $expectsMethodCall) : Closure
    {
        $closure = new Closure();
        $matcherVariable = new Variable('matcher');
        $closure->uses[] = new ClosureUse($matcherVariable);
        $usedVariables = $this->resolveUniqueUsedVariables($expectsMethodCall);
        foreach ($usedVariables as $usedVariable) {
            $closure->uses[] = new ClosureUse($usedVariable);
        }
        $match = $this->createMatch($matcherVariable, $expectsMethodCall);
        $closure->stmts[] = new Return_($match);
        return $closure;
    }
    /**
     * Replace $this->expects(...)
     *
     * @param Expression<MethodCall> $expression
     */
    private function matchAndRefactorExpectsMethodCall(Expression $expression) : ?MethodCall
    {
        /** @var MethodCall|null $exactlyMethodCall */
        $exactlyMethodCall = null;
        $this->traverseNodesWithCallable($expression, function (Node $node) use(&$exactlyMethodCall) : ?MethodCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'expects')) {
                return null;
            }
            $firstArg = $node->getArgs()[0];
            if (!$firstArg->value instanceof MethodCall) {
                return null;
            }
            $exactlyMethodCall = $firstArg->value;
            $node->args = [new Arg(new Variable('matcher'))];
            return $node;
        });
        return $exactlyMethodCall;
    }
    private function findWithConsecutiveMethodCall(Expression $expression) : ?MethodCall
    {
        if (!$expression->expr instanceof MethodCall) {
            return null;
        }
        /** @var MethodCall|null $withConsecutiveMethodCall */
        $withConsecutiveMethodCall = $this->betterNodeFinder->findFirst($expression->expr, function (Node $node) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->isName($node->name, 'withConsecutive');
        });
        return $withConsecutiveMethodCall;
    }
    private function createMatch(Variable $matcherVariable, MethodCall $expectsMethodCall) : Match_
    {
        $numberOfInvocationsMethodCall = new MethodCall($matcherVariable, new Identifier('numberOfInvocations'));
        $matchArms = [];
        foreach ($expectsMethodCall->getArgs() as $key => $arg) {
            $matchArms[] = new MatchArm([new LNumber($key + 1)], $arg->value);
        }
        return new Match_($numberOfInvocationsMethodCall, $matchArms);
    }
    /**
     * @return Variable[]
     */
    private function resolveUniqueUsedVariables(MethodCall $expectsMethodCall) : array
    {
        /** @var Variable[] $usedVariables */
        $usedVariables = $this->findInstancesOfScoped($expectsMethodCall->getArgs(), Variable::class);
        $uniqueUsedVariables = [];
        foreach ($usedVariables as $usedVariable) {
            if ($this->isName($usedVariable, 'this')) {
                continue;
            }
            $usedVariableName = $this->getName($usedVariable);
            $uniqueUsedVariables[$usedVariableName] = $usedVariable;
        }
        return $uniqueUsedVariables;
    }
}
