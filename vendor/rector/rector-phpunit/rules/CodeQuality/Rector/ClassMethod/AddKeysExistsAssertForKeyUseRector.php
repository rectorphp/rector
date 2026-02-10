<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\AssertHasKeyMatcher;
use Rector\PHPUnit\CodeQuality\NodeFactory\AssertArrayHasKeyCallFactory;
use Rector\PHPUnit\CodeQuality\ValueObject\VariableAndDimFetch;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\AddKeysExistsAssertForKeyUseRector\AddKeysExistsAssertForKeyUseRectorTest
 */
final class AddKeysExistsAssertForKeyUseRector extends AbstractRector
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
    private AssertHasKeyMatcher $assertHasKeyMatcher;
    /**
     * @readonly
     */
    private AssertArrayHasKeyCallFactory $assertArrayHasKeyCallFactory;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver, AssertHasKeyMatcher $assertHasKeyMatcher, AssertArrayHasKeyCallFactory $assertArrayHasKeyCallFactory)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->assertHasKeyMatcher = $assertHasKeyMatcher;
        $this->assertArrayHasKeyCallFactory = $assertArrayHasKeyCallFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add assertArrayHasKey() call for array access with string key, that was not validated before', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $data = $this->getData();
        $this->assertSame('result', $data['key']);
    }

    private function getData(): array
    {
        // return various data
        return [];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $data = $this->getData();
        $this->assertArrayHasKey('key', $data);
        $this->assertSame('result', $data['key']);
    }

    private function getData(): array
    {
        // return various data
        return [];
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
        if ($node->stmts === [] || $node->stmts === null || count($node->stmts) < 2) {
            return null;
        }
        $knownVariableDimFetches = [];
        $next = 0;
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $variableAndDimFetch = $this->assertHasKeyMatcher->match($stmt);
            if ($variableAndDimFetch instanceof VariableAndDimFetch) {
                $knownVariableDimFetches[] = $variableAndDimFetch;
                continue;
            }
            if (!$this->testsNodeAnalyzer->isAssertMethodCallName($stmt->expr, 'assertSame')) {
                continue;
            }
            /** @var StaticCall|MethodCall $call */
            $call = $stmt->expr;
            $assertedArg = $call->getArgs()[1];
            $assertedExpr = $assertedArg->value;
            if (!$assertedExpr instanceof ArrayDimFetch) {
                continue;
            }
            if (!$assertedExpr->var instanceof Variable) {
                continue;
            }
            if (!$assertedExpr->dim instanceof Expr) {
                continue;
            }
            $dimFetchVariableName = $this->getName($assertedExpr->var);
            if ($dimFetchVariableName === null) {
                continue;
            }
            // already known dim, lets skip
            if ($this->isKnownDimFetch($knownVariableDimFetches, $dimFetchVariableName, $assertedExpr->dim)) {
                continue;
            }
            $scope = ScopeFetcher::fetch($node);
            $callExpression = $this->assertArrayHasKeyCallFactory->create($assertedExpr->var, $assertedExpr->dim, $scope);
            array_splice($node->stmts, $key + $next, 0, [$callExpression]);
            ++$next;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param VariableAndDimFetch[] $knownVariableDimFetches
     */
    private function isKnownDimFetch(array $knownVariableDimFetches, string $dimFetchVariableName, Expr $dimExpr): bool
    {
        foreach ($knownVariableDimFetches as $knownVariableDimFetch) {
            if (!$this->isName($knownVariableDimFetch->getVariable(), $dimFetchVariableName)) {
                continue;
            }
            $dimExprValue = $this->valueResolver->getValue($dimExpr);
            if ($this->valueResolver->isValue($knownVariableDimFetch->getDimFetchExpr(), $dimExprValue)) {
                return \true;
            }
        }
        return \false;
    }
}
