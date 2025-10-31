<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Return_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\MethodCall\MergeWithCallableAndWillReturnRector\MergeWithCallableAndWillReturnRectorTest
 */
final class MergeWithCallableAndWillReturnRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Merge split mocking method ->with($this->callback(...)) and ->willReturn(expr) to single ->willReturnCallback() call', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->createMock('SomeClass')
            ->expects($this->once())
            ->method('someMethod')
            ->with($this->callback(function (array $args): bool {
                return true;
            }))
            ->willReturn(['some item']);
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
            ->willReturnCallback(function (array $args): array {
                return ['some item'];
            });
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
        if (!$this->isName($node->name, 'willReturn')) {
            return null;
        }
        $parentCaller = $node->var;
        if (!$parentCaller instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($parentCaller->name, 'with')) {
            return null;
        }
        $willReturnMethodCall = $node;
        $withMethodCall = $parentCaller;
        $callbackMethodCall = $this->matchFirstArgCallbackMethodCall($withMethodCall);
        if (!$callbackMethodCall instanceof MethodCall) {
            return null;
        }
        $innerClosure = $callbackMethodCall->getArgs()[0]->value;
        if (!$innerClosure instanceof Closure) {
            return null;
        }
        if ($innerClosure->stmts === []) {
            return null;
        }
        if (!$this->isLastStmtReturnTrue($innerClosure)) {
            return null;
        }
        /** @var Return_ $return */
        $return = $innerClosure->stmts[count($innerClosure->stmts) - 1];
        $returnedExpr = $willReturnMethodCall->getArgs()[0]->value;
        $return->expr = $returnedExpr;
        $parentCaller->name = new Identifier('willReturnCallback');
        $parentCaller->args = [new Arg($innerClosure)];
        if ($returnedExpr instanceof Variable) {
            $innerClosure->uses[] = new ClosureUse($returnedExpr);
        }
        $returnedExprType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnedExpr);
        $innerClosure->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnedExprType, TypeKind::RETURN);
        return $parentCaller;
    }
    private function matchFirstArgCallbackMethodCall(MethodCall $withMethodCall): ?MethodCall
    {
        $firstArgValue = $withMethodCall->getArgs()[0]->value;
        if (!$firstArgValue instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($firstArgValue->name, 'callback')) {
            return null;
        }
        return $firstArgValue;
    }
    private function isLastStmtReturnTrue(Closure $closure): bool
    {
        $lastStmt = $closure->stmts[count($closure->stmts) - 1];
        if (!$lastStmt instanceof Return_) {
            return \false;
        }
        if (!$lastStmt->expr instanceof Node) {
            return \false;
        }
        return $this->valueResolver->isTrue($lastStmt->expr);
    }
}
