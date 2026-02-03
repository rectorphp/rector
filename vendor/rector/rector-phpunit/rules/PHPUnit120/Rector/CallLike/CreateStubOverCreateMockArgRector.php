<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\CallLike;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\MockObjectExprDetector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Related change in PHPUnit 12 https://phpunit.expert/articles/testing-with-and-without-dependencies.html
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\CallLike\CreateStubOverCreateMockArgRector\CreateStubOverCreateMockArgRectorTest
 */
final class CreateStubOverCreateMockArgRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private MockObjectExprDetector $mockObjectExprDetector;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, MockObjectExprDetector $mockObjectExprDetector)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->mockObjectExprDetector = $mockObjectExprDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use createStub() over createMock() when used as argument or array value and does not add any mock requirements', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
final class SomeTest extends TestCase
{
    public function test()
    {
        $this->someMethod($this->createMock(SomeClass::class));
    }

    private function someMethod($someClass)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->someMethod($this->createStub(SomeClass::class));
    }

    private function someMethod($someClass)
    {
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
        return [StaticCall::class, MethodCall::class, New_::class, ArrayItem::class, ClassMethod::class];
    }
    /**
     * @param MethodCall|StaticCall|New_|ArrayItem|ClassMethod $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\ArrayItem|\PhpParser\Node\Stmt\ClassMethod|null
     */
    public function refactor(Node $node)
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        if ($node instanceof ArrayItem) {
            return $this->refactorArrayItem($node);
        }
        $hasChanges = \false;
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach ($node->getArgs() as $arg) {
            if (!$arg->value instanceof MethodCall) {
                continue;
            }
            $methodCall = $arg->value;
            if (!$this->isName($methodCall->name, 'createMock')) {
                continue;
            }
            $methodCall->name = new Identifier('createStub');
            $hasChanges = \true;
        }
        if ($hasChanges) {
            return $node;
        }
        return null;
    }
    private function matchCreateMockMethodCall(Expr $expr): ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$expr instanceof MethodCall) {
            return null;
        }
        if (!$this->isName($expr->name, 'createMock')) {
            return null;
        }
        return $expr;
    }
    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
            return null;
        }
        $hasChanged = \false;
        foreach ((array) $classMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            // handled in another rule
            if ($assign->var instanceof PropertyFetch) {
                continue;
            }
            $createMockMethodCall = $this->matchCreateMockMethodCall($assign->expr);
            if (!$createMockMethodCall instanceof MethodCall) {
                continue;
            }
            // no change, as we use the variable for mocking later
            if ($this->mockObjectExprDetector->isUsedForMocking($assign->var, $classMethod)) {
                continue;
            }
            $createMockMethodCall->name = new Identifier('createStub');
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $classMethod;
        }
        return null;
    }
    private function refactorArrayItem(ArrayItem $arrayItem): ?ArrayItem
    {
        if (!$arrayItem->value instanceof MethodCall) {
            return null;
        }
        $methodCall = $arrayItem->value;
        if (!$this->isName($methodCall->name, 'createMock')) {
            return null;
        }
        $methodCall->name = new Identifier('createStub');
        return $arrayItem;
    }
}
