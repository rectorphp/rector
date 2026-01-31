<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\AssignedMocksCollector;
use Rector\PHPUnit\CodeQuality\NodeFinder\VariableFinder;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\ClassMethod\ExpressionCreateMockToCreateStubRector\ExpressionCreateMockToCreateStubRectorTest
 */
final class ExpressionCreateMockToCreateStubRector extends AbstractRector
{
    /**
     * @readonly
     */
    private AssignedMocksCollector $assignedMocksCollector;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private VariableFinder $variableFinder;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(AssignedMocksCollector $assignedMocksCollector, TestsNodeAnalyzer $testsNodeAnalyzer, VariableFinder $variableFinder, BetterNodeFinder $betterNodeFinder)
    {
        $this->assignedMocksCollector = $assignedMocksCollector;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->variableFinder = $variableFinder;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace createMock() assigned to variable that is only used as arg with no expectations, to createStub()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $mock = $this->createMock(SomeClass::class);

        $someObject = new SomeClass($mock);
        $this->assertSame($mock, $someObject->getDependency());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $mock = $this->createStub(SomeClass::class);

        $someObject = new SomeClass($mock);
        $this->assertSame($mock, $someObject->getDependency());
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        if (!$this->testsNodeAnalyzer->isTestClassMethod($node)) {
            return null;
        }
        if ($node->stmts === null || count($node->stmts) < 2) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $typeArg = $this->assignedMocksCollector->matchCreateMockArgAssignedToVariable($stmt->expr);
            if (!$typeArg instanceof Arg) {
                continue;
            }
            /** @var Assign $assign */
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            $assignedVariable = $assign->var;
            $variableName = $this->getName($assignedVariable);
            if ($variableName === null) {
                continue;
            }
            // find variable usages outside call like and inside it
            $usedVariables = $this->variableFinder->find($node, $variableName);
            // used variable in calls
            /** @var array<StaticCall|MethodCall|New_> $callLikes */
            $callLikes = $this->betterNodeFinder->findInstancesOfScoped($node->stmts, [CallLike::class]);
            $callLikeUsedVariables = $this->collectVariableInCallLikeArg($callLikes, $variableName);
            if (count($usedVariables) - 1 !== count($callLikeUsedVariables)) {
                continue;
            }
            // here we can flip the createMock() to createStub()
            if (!$assign->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $assign->expr;
            $methodCall->name = new Identifier('createStub');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param CallLike[] $callLikes
     * @return Variable[]
     */
    private function collectVariableInCallLikeArg(array $callLikes, string $variableName): array
    {
        $callLikeUsedVariables = [];
        foreach ($callLikes as $callLike) {
            if ($callLike->isFirstClassCallable()) {
                continue;
            }
            foreach ($callLike->getArgs() as $arg) {
                if (!$arg->value instanceof Variable) {
                    continue;
                }
                if (!$this->isName($arg->value, $variableName)) {
                    continue;
                }
                $callLikeUsedVariables[] = $arg->value;
            }
        }
        return $callLikeUsedVariables;
    }
}
