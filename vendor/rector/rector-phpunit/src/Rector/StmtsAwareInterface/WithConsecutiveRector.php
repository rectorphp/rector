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
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\Rector\StmtsAwareInterface\WithConsecutiveRector\WithConsecutiveRectorTest
 */
final class WithConsecutiveRector extends AbstractRector
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
        return new RuleDefinition('Refactor "withConsecutive()" to ', [new CodeSample(<<<'CODE_SAMPLE'
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
        if (!$node->expr instanceof MethodCall) {
            return null;
        }
        /** @var MethodCall|null $withConsecutiveMethodCall */
        $withConsecutiveMethodCall = $this->betterNodeFinder->findFirst($node->expr, function (Node $node) : bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->isName($node->name, 'withConsecutive');
        });
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
    private function createClosure(MethodCall $expectsMethodCall) : Closure
    {
        $closure = new Closure();
        $matcherVariable = new Variable('matcher');
        $closure->uses[] = new ClosureUse($matcherVariable);
        $match = new Match_(new MethodCall($matcherVariable, new Identifier('numberOfInvocations')));
        foreach ($expectsMethodCall->getArgs() as $key => $arg) {
            $match->arms[] = new MatchArm([new LNumber($key + 1)], $arg->value);
        }
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
}
