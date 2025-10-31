<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Node\BetterNodeFinder;
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
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private FromBinaryAndAssertExpressionsFactory $fromBinaryAndAssertExpressionsFactory;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, FromBinaryAndAssertExpressionsFactory $fromBinaryAndAssertExpressionsFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->fromBinaryAndAssertExpressionsFactory = $fromBinaryAndAssertExpressionsFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replaces identical compare in $this->callable() to standalone PHPUnit asserts', [new CodeSample(<<<'CODE_SAMPLE'
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
        $argAndFunctionLike = $this->matchWithCallbackInnerClosure($node);
        if (!$argAndFunctionLike instanceof ArgAndFunctionLike) {
            return null;
        }
        if (!$argAndFunctionLike->hasParams()) {
            return null;
        }
        $innerSoleExpr = $this->matchInnerSoleExpr($argAndFunctionLike->getFunctionLike());
        if (!$innerSoleExpr instanceof BooleanAnd) {
            return null;
        }
        $joinedExprs = $this->extractJoinedExprs($innerSoleExpr);
        if ($joinedExprs === null || $joinedExprs === []) {
            return null;
        }
        $assertExpressions = $this->fromBinaryAndAssertExpressionsFactory->create($joinedExprs);
        if ($assertExpressions === null) {
            return null;
        }
        // last si return true;
        $assertExpressions[] = new Return_($this->nodeFactory->createTrue());
        $innerFunctionLike = $argAndFunctionLike->getFunctionLike();
        if ($innerFunctionLike instanceof Closure) {
            $innerFunctionLike->stmts = $assertExpressions;
        } else {
            // arrow function -> flip to closure
            $functionLikeInArg = $argAndFunctionLike->getArg();
            $externalVariables = $this->resolveExternalClosureUses($innerFunctionLike);
            $closure = new Closure(['params' => $argAndFunctionLike->getFunctionLike()->params, 'stmts' => $assertExpressions, 'returnType' => new Identifier('void'), 'uses' => $externalVariables]);
            $functionLikeInArg->value = $closure;
        }
        return $node;
    }
    /**
     * @return Expr[]|null
     */
    private function extractJoinedExprs(BooleanAnd $booleanAnd): ?array
    {
        // must be full queue of BooleanAnds
        $joinedExprs = [];
        $currentNode = $booleanAnd;
        do {
            // is binary op, but not "&&"
            if ($currentNode->right instanceof BooleanOr) {
                return null;
            }
            $joinedExprs[] = $currentNode->right;
            $currentNode = $currentNode->left;
        } while ($currentNode instanceof BooleanAnd);
        $joinedExprs[] = $currentNode;
        return $joinedExprs;
    }
    private function matchWithCallbackInnerClosure(MethodCall $methodCall): ?\Rector\PHPUnit\CodeQuality\ValueObject\ArgAndFunctionLike
    {
        if (!$this->isName($methodCall->name, 'with')) {
            return null;
        }
        $firstArg = $methodCall->getArgs()[0];
        if (!$firstArg->value instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($firstArg->value->name, 'callback')) {
            return null;
        }
        $callbackMethodCall = $firstArg->value;
        $innerFirstArg = $callbackMethodCall->getArgs()[0];
        if ($innerFirstArg->value instanceof Closure || $innerFirstArg->value instanceof ArrowFunction) {
            return new ArgAndFunctionLike($innerFirstArg, $innerFirstArg->value);
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function matchInnerSoleExpr($functionLike): ?Expr
    {
        if ($functionLike instanceof Closure) {
            if (count($functionLike->stmts) !== 1) {
                return null;
            }
            $innerStmt = $functionLike->stmts[0];
            if (!$innerStmt instanceof Return_) {
                return null;
            }
            return $innerStmt->expr;
        }
        return $functionLike->expr;
    }
    /**
     * @return ClosureUse[]
     */
    private function resolveExternalClosureUses(ArrowFunction $arrowFunction): array
    {
        // fill needed uses from arrow function to closure
        $arrowFunctionVariables = $this->betterNodeFinder->findInstancesOfScoped($arrowFunction->getStmts(), Variable::class);
        $paramNames = [];
        foreach ($arrowFunction->getParams() as $param) {
            $paramNames[] = $this->getName($param);
        }
        $externalVariableNames = [];
        foreach ($arrowFunctionVariables as $arrowFunctionVariable) {
            // skip those defined in params
            if ($this->isNames($arrowFunctionVariable, $paramNames)) {
                continue;
            }
            $variableName = $this->getName($arrowFunctionVariable);
            if (!is_string($variableName)) {
                continue;
            }
            $externalVariableNames[] = $variableName;
        }
        $externalVariableNames = array_unique($externalVariableNames);
        $externalVariableNames = array_diff($externalVariableNames, ['this']);
        $closureUses = [];
        foreach ($externalVariableNames as $externalVariableName) {
            $closureUses[] = new ClosureUse(new Variable($externalVariableName));
        }
        return $closureUses;
    }
}
