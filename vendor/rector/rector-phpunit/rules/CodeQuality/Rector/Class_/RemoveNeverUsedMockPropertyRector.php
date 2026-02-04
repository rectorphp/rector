<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\MockObjectPropertyDetector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\RemoveNeverUsedMockPropertyRector\RemoveNeverUsedMockPropertyRectorTest
 */
final class RemoveNeverUsedMockPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private MockObjectPropertyDetector $mockObjectPropertyDetector;
    /**
     * @readonly
     */
    private PropertyFetchFinder $propertyFetchFinder;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, MockObjectPropertyDetector $mockObjectPropertyDetector, PropertyFetchFinder $propertyFetchFinder, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->mockObjectPropertyDetector = $mockObjectPropertyDetector;
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove never used property mock, only to set expectations', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $mockProperty = null;

    protected function setUp(): void
    {
        $this->mockProperty = $this->createMock(SomeClass::class);

        $this->mockProperty->expects($this->once())
            ->method('someMethod')
            ->willReturn('someValue');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    protected function setUp(): void
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return null;
        }
        $propertyNamesToCreateMockMethodCalls = $this->mockObjectPropertyDetector->collectFromClassMethod($setUpClassMethod);
        if ($propertyNamesToCreateMockMethodCalls === []) {
            return null;
        }
        $propertyNamesToRemove = $this->resolvePropertyNamesToRemove($propertyNamesToCreateMockMethodCalls, $node);
        if ($propertyNamesToRemove === []) {
            return null;
        }
        // remove never used mock properties
        foreach ($propertyNamesToRemove as $propertyNameToRemove) {
            // 1. remove property
            $this->removePropertyFromClass($node, $propertyNameToRemove);
            // 2. remove assign from setUp()
            $this->removeMockPropertyFromSetUpMethod($setUpClassMethod, $propertyNameToRemove);
            // 3. remove expression method calls on this property
            foreach ($node->getMethods() as $classMethod) {
                foreach ((array) $classMethod->stmts as $key => $classMethodStmt) {
                    if (!$classMethodStmt instanceof Expression) {
                        continue;
                    }
                    if (!$classMethodStmt->expr instanceof MethodCall) {
                        continue;
                    }
                    $methodCall = $classMethodStmt->expr;
                    $currentMethodCall = $methodCall;
                    while ($currentMethodCall->var instanceof MethodCall) {
                        $currentMethodCall = $currentMethodCall->var;
                    }
                    if (!$currentMethodCall->var instanceof PropertyFetch) {
                        continue;
                    }
                    $propertyFetch = $currentMethodCall->var;
                    if (!$this->isName($propertyFetch->name, $propertyNameToRemove)) {
                        continue;
                    }
                    unset($classMethod->stmts[$key]);
                }
            }
        }
        return $node;
    }
    private function removeMockPropertyFromSetUpMethod(ClassMethod $setUpClassMethod, string $propertyName): void
    {
        foreach ((array) $setUpClassMethod->stmts as $key => $classStmt) {
            if (!$classStmt instanceof Expression) {
                continue;
            }
            if (!$classStmt->expr instanceof Assign) {
                continue;
            }
            $assign = $classStmt->expr;
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            $assignedPropertyFetch = $assign->var;
            if (!$this->isName($assignedPropertyFetch->name, $propertyName)) {
                continue;
            }
            unset($setUpClassMethod->stmts[$key]);
            return;
        }
    }
    private function removePropertyFromClass(Class_ $class, string $propertyNameToRemove): void
    {
        foreach ($class->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            if (!$this->isName($stmt, $propertyNameToRemove)) {
                continue;
            }
            unset($class->stmts[$key]);
        }
    }
    /**
     * @param array<string, MethodCall> $propertyNamesToCreateMockMethodCalls
     * @return string[]
     */
    private function resolvePropertyNamesToRemove(array $propertyNamesToCreateMockMethodCalls, Class_ $class): array
    {
        $propertyNamesToRemove = [];
        foreach (array_keys($propertyNamesToCreateMockMethodCalls) as $propertyName) {
            $allPropertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($class, $propertyName);
            /** @var MethodCall[] $methodCalls */
            $methodCalls = $this->betterNodeFinder->findInstancesOfScoped($class->getMethods(), MethodCall::class);
            $propertyFetchesMethodCalls = [];
            foreach ($methodCalls as $methodCall) {
                if ($methodCall->isFirstClassCallable()) {
                    continue;
                }
                if (!$methodCall->var instanceof PropertyFetch) {
                    continue;
                }
                $propertyFetch = $methodCall->var;
                if (!$this->isName($propertyFetch->name, $propertyName)) {
                    continue;
                }
                // used in method call, skip removal
                $propertyFetchesMethodCalls[] = $methodCall;
            }
            // -1 for the assign in setUp() method
            if (count($allPropertyFetches) - 1 !== count($propertyFetchesMethodCalls)) {
                continue;
            }
            $propertyNamesToRemove[] = $propertyName;
        }
        return $propertyNamesToRemove;
    }
}
