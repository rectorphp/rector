<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Return_;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\ClosureUsesResolver;
use Rector\PHPUnit\CodeQuality\NodeFactory\FromBinaryAndAssertExpressionsFactory;
use Rector\PHPUnit\CodeQuality\ValueObject\ArgAndFunctionLike;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\WithCallbackIdenticalToStandaloneAssertsRector\WithCallbackIdenticalToStandaloneAssertsRectorTest
 */
final class WithCallbackIdenticalToStandaloneAssertsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ClosureUsesResolver $closureUsesResolver;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private FromBinaryAndAssertExpressionsFactory $fromBinaryAndAssertExpressionsFactory;
    public function __construct(ClosureUsesResolver $closureUsesResolver, TestsNodeAnalyzer $testsNodeAnalyzer, FromBinaryAndAssertExpressionsFactory $fromBinaryAndAssertExpressionsFactory)
    {
        $this->closureUsesResolver = $closureUsesResolver;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->fromBinaryAndAssertExpressionsFactory = $fromBinaryAndAssertExpressionsFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces identical compare in $this->callable() sole return to standalone PHPUnit asserts that show more detailed failure messages', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->createMock('SomeClass')
            ->expects($this->once())
            ->method('someMethod')
            ->with($this->callback(function (array $args): bool {
                return count($args) === 2 && $args[0] === 'correct'
            }));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->createMock('SomeClass')
            ->expects($this->once())
            ->method('someMethod')
            ->with($this->callback(function (array $args): bool {
                $this->assertCount(2, $args);
                $this->assertSame('correct', $args[0]);

                return true;
            }));
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<MethodCall>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Expr\MethodCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        if (!$this->isName($node->name, 'with')) {
            return null;
        }
        foreach ($node->getArgs() as $arg) {
            $argAndFunctionLike = $this->matchCallbackArgAndFunctionLike($arg);
            if (!$argAndFunctionLike instanceof ArgAndFunctionLike) {
                continue;
            }
            $joinedExprs = $this->resolveInnerReturnJoinedExprs($argAndFunctionLike);
            if ($joinedExprs === []) {
                continue;
            }
            $assertExprStmts = $this->fromBinaryAndAssertExpressionsFactory->create($joinedExprs);
            if ($assertExprStmts === []) {
                continue;
            }
            $nonReturnCallbackStmts = $this->resolveNonReturnCallbackStmts($argAndFunctionLike);
            // last si return true;
            $assertExprStmts[] = new Return_($this->nodeFactory->createTrue());
            $innerFunctionLike = $argAndFunctionLike->getFunctionLike();
            if ($innerFunctionLike instanceof Closure) {
                $innerFunctionLike->stmts = array_merge($nonReturnCallbackStmts, $assertExprStmts);
            } else {
                // arrow function -> flip to closure
                $functionLikeInArg = $argAndFunctionLike->getArg();
                $closure = $this->createClosure($innerFunctionLike, $argAndFunctionLike, $assertExprStmts);
                $functionLikeInArg->value = $closure;
            }
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return Expr[]
     */
    private function extractJoinedExprs(BooleanAnd $booleanAnd): array
    {
        // must be full queue of BooleanAnds
        $joinedExprs = [];
        $currentNode = $booleanAnd;
        do {
            // is binary op, but not "&&"
            if ($currentNode->right instanceof BooleanOr) {
                return [];
            }
            $joinedExprs[] = $currentNode->right;
            $currentNode = $currentNode->left;
        } while ($currentNode instanceof BooleanAnd);
        $joinedExprs[] = $currentNode;
        return $joinedExprs;
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function matchInnerSoleExpr($functionLike): ?Expr
    {
        if ($functionLike instanceof Closure) {
            foreach ($functionLike->getStmts() as $stmt) {
                if (!$stmt instanceof Return_) {
                    continue;
                }
                return $stmt->expr;
            }
            return null;
        }
        return $functionLike->expr;
    }
    private function matchCallbackArgAndFunctionLike(Arg $arg): ?ArgAndFunctionLike
    {
        if (!$arg->value instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($arg->value->name, 'callback')) {
            return null;
        }
        $callbackMethodCall = $arg->value;
        $innerFirstArg = $callbackMethodCall->getArgs()[0];
        if (!$innerFirstArg->value instanceof Closure && !$innerFirstArg->value instanceof ArrowFunction) {
            return null;
        }
        return new ArgAndFunctionLike($innerFirstArg, $innerFirstArg->value);
    }
    /**
     * @return Expr[]
     */
    private function resolveInnerReturnJoinedExprs(ArgAndFunctionLike $argAndFunctionLike): array
    {
        $innerSoleExpr = $this->matchInnerSoleExpr($argAndFunctionLike->getFunctionLike());
        if ($innerSoleExpr instanceof BooleanAnd) {
            return $this->extractJoinedExprs($innerSoleExpr);
        }
        if ($innerSoleExpr instanceof Identical || $innerSoleExpr instanceof Instanceof_ || $innerSoleExpr instanceof Isset_ || $innerSoleExpr instanceof FuncCall && $this->isName($innerSoleExpr->name, 'array_key_exists') || $innerSoleExpr instanceof Equal) {
            return [$innerSoleExpr];
        }
        return [];
    }
    /**
     * @return Stmt[]
     */
    private function resolveNonReturnCallbackStmts(ArgAndFunctionLike $argAndFunctionLike): array
    {
        // all stmts but last
        $functionLike = $argAndFunctionLike->getFunctionLike();
        if ($functionLike instanceof Closure) {
            $functionStmts = $functionLike->stmts;
            foreach ($functionStmts as $key => $value) {
                if (!$value instanceof Return_) {
                    continue;
                }
                unset($functionStmts[$key]);
            }
            return $functionStmts;
        }
        return [];
    }
    /**
     * @param Stmt[] $assertExprStmts
     */
    private function createClosure(ArrowFunction $arrowFunction, ArgAndFunctionLike $argAndFunctionLike, array $assertExprStmts): Closure
    {
        $externalVariables = $this->closureUsesResolver->resolveFromArrowFunction($arrowFunction);
        return new Closure(['params' => $argAndFunctionLike->getFunctionLike()->params, 'stmts' => $assertExprStmts, 'returnType' => new Identifier('bool'), 'uses' => $externalVariables]);
    }
}
