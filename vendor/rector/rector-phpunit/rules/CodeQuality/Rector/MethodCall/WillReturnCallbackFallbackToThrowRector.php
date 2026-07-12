<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\PHPUnit\Enum\ConsecutiveVariable;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\MatcherInvocationCountMethodCallNodeFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\WillReturnCallbackFallbackToThrowRector\WillReturnCallbackFallbackToThrowRectorTest
 */
final class WillReturnCallbackFallbackToThrowRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private MatcherInvocationCountMethodCallNodeFactory $matcherInvocationCountMethodCallNodeFactory;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, MatcherInvocationCountMethodCallNodeFactory $matcherInvocationCountMethodCallNodeFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->matcherInvocationCountMethodCallNodeFactory = $matcherInvocationCountMethodCallNodeFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add a throw fallback to a consecutive willReturnCallback() that has no explicit fallback return, so an unexpected extra call fails loudly', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $matcher = $this->exactly(1);

        $this->someServiceMock->expects($matcher)
            ->method('run')
            ->willReturnCallback(function () use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    return 1;
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
        $matcher = $this->exactly(1);

        $this->someServiceMock->expects($matcher)
            ->method('run')
            ->willReturnCallback(function () use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    return 1;
                }

                throw new \PHPUnit\Framework\Exception(sprintf('Method should not be called for the %dth time', $matcher->numberOfInvocations()));
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
        if (!$this->usesMatcher($closure)) {
            return null;
        }
        // the closure must branch on the matcher invocation count first
        if (!$this->hasConsecutiveIf($closure)) {
            return null;
        }
        $lastStmt = $closure->stmts[array_key_last($closure->stmts)] ?? null;
        // the last statement must be an if branch; an explicit fallback "return <expr>;" is left untouched
        if (!$lastStmt instanceof If_) {
            return null;
        }
        $closure->stmts[] = $this->createThrow();
        return $node;
    }
    private function usesMatcher(Closure $closure): bool
    {
        $found = \false;
        foreach ($closure->uses as $use) {
            if ($this->isName($use->var, ConsecutiveVariable::MATCHER)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    private function hasConsecutiveIf(Closure $closure): bool
    {
        foreach ($closure->stmts as $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            $hasMatcherCall = \false;
            $this->traverseNodesWithCallable($stmt->cond, function (Node $node) use (&$hasMatcherCall) {
                if ($node instanceof MethodCall && $node->var instanceof Variable && $this->isName($node->var, ConsecutiveVariable::MATCHER)) {
                    $hasMatcherCall = \true;
                }
                return null;
            });
            if ($hasMatcherCall) {
                return \true;
            }
        }
        return \false;
    }
    private function createThrow(): Expression
    {
        $sprintfFuncCall = $this->nodeFactory->createFuncCall('sprintf', [new String_('Method should not be called for the %dth time'), $this->matcherInvocationCountMethodCallNodeFactory->create()]);
        $new = new New_(new FullyQualified('PHPUnit\Framework\Exception'), [new Arg($sprintfFuncCall)]);
        return new Expression(new Throw_($new));
    }
}
