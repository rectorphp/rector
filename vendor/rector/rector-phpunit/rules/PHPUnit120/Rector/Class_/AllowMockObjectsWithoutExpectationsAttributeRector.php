<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\Enum\PHPUnitAttribute;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\Class_\AllowMockObjectsWithoutExpectationsAttributeRector\AllowMockObjectsWithoutExpectationsAttributeRectorTest
 *
 * @see https://github.com/sebastianbergmann/phpunit/commit/24c208d6a340c3071f28a9b5cce02b9377adfd43
 */
final class AllowMockObjectsWithoutExpectationsAttributeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private AttributeFinder $attributeFinder;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AttributeFinder $attributeFinder, ReflectionProvider $reflectionProvider, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->attributeFinder = $attributeFinder;
        $this->reflectionProvider = $reflectionProvider;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        // even for 0 mocked properties, the variable in setUp() can be mocked
        $mockObjectPropertyNames = $this->matchMockObjectPropertyNames($node);
        $missedTestMethodsByMockPropertyName = [];
        $usingTestMethodsByMockPropertyName = [];
        $testMethodCount = 0;
        foreach ($mockObjectPropertyNames as $mockObjectPropertyName) {
            $missedTestMethodsByMockPropertyName[$mockObjectPropertyName] = [];
            $usingTestMethodsByMockPropertyName[$mockObjectPropertyName] = [];
            foreach ($node->getMethods() as $classMethod) {
                if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
                    continue;
                }
                ++$testMethodCount;
                // is a mock property used in the class method, as part of some method call? guessing mock expectation is set
                // skip if so
                if ($this->isClassMethodUsingMethodCallOnPropertyNamed($classMethod, $mockObjectPropertyName)) {
                    $usingTestMethodsByMockPropertyName[$mockObjectPropertyName][] = $this->getName($classMethod);
                    continue;
                }
                $missedTestMethodsByMockPropertyName[$mockObjectPropertyName][] = $this->getName($classMethod);
            }
        }
        // or find a ->method() calls on a setUp() mocked property
        $hasAnyMethodInSetup = $this->isMissingExpectsOnMockObjectMethodCallInSetUp($node);
        if ($hasAnyMethodInSetup) {
            $node->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS))]);
            return $node;
        }
        if (!$this->shouldAddAttribute($missedTestMethodsByMockPropertyName)) {
            return null;
        }
        // skip sole test method, as those are expected to use all mocks
        if ($testMethodCount < 2) {
            return null;
        }
        if (!$this->isAtLeastOneMockPropertyMockedOnce($usingTestMethodsByMockPropertyName)) {
            return null;
        }
        // add attribute
        $node->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS))]);
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add #[AllowMockObjectsWithoutExpectations] attribute to PHPUnit test classes with mock properties used in multiple methods but one, to avoid irrelevant notices in tests run', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
final class SomeTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $someServiceMock;

    protected function setUp(): void
    {
        $this->someServiceMock = $this->createMock(SomeService::class);
    }

    public function testOne(): void
    {
        // use $this->someServiceMock
    }

    public function testTwo(): void
    {
        // use $this->someServiceMock
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

#[AllowMockObjectsWithoutExpectations]
final class SomeTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $someServiceMock;

    protected function setUp(): void
    {
        $this->someServiceMock = $this->createMock(SomeService::class);
    }

    public function testOne(): void
    {
        $this->someServiceMock->expects($this->once())
            ->method('someMethod')
            ->willReturn('someValue');
    }

    public function testTwo(): void
    {
        $this->someServiceMock->expects($this->once())
            ->method('someMethod')
            ->willReturn('anotherValue');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return string[]
     */
    private function matchMockObjectPropertyNames(Class_ $class): array
    {
        $propertyNames = [];
        foreach ($class->getProperties() as $property) {
            if (!$property->type instanceof Name) {
                continue;
            }
            if (!$this->isName($property->type, PHPUnitClassName::MOCK_OBJECT)) {
                continue;
            }
            $propertyNames[] = $this->getName($property->props[0]);
        }
        return $propertyNames;
    }
    private function shouldSkipClass(Class_ $class): bool
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($class)) {
            return \true;
        }
        // attribute must exist for the rule to work
        if (!$this->reflectionProvider->hasClass(PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS)) {
            return \true;
        }
        // already filled
        if ($this->attributeFinder->hasAttributeByClasses($class, [PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS])) {
            return \true;
        }
        // has mock objects properties and setUp() method?
        $setupClassMethod = $class->getMethod(MethodName::SET_UP);
        return !$setupClassMethod instanceof ClassMethod;
    }
    private function isClassMethodUsingMethodCallOnPropertyNamed(ClassMethod $classMethod, string $mockObjectPropertyName): bool
    {
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstancesOfScoped([$classMethod], [MethodCall::class]);
        foreach ($methodCalls as $methodCall) {
            if (!$methodCall->var instanceof PropertyFetch) {
                continue;
            }
            $propertyFetch = $methodCall->var;
            // we found a method call on a property fetch named
            if ($this->isName($propertyFetch, $mockObjectPropertyName)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param array<string, string[]> $missedTestMethodsByMockPropertyName
     */
    private function shouldAddAttribute(array $missedTestMethodsByMockPropertyName): bool
    {
        foreach ($missedTestMethodsByMockPropertyName as $missedTestMethods) {
            // all test methods are using method calls on the mock property, so skip
            if (count($missedTestMethods) === 0) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param array<string, string[]> $usingTestMethodsByMockPropertyName
     */
    private function isAtLeastOneMockPropertyMockedOnce(array $usingTestMethodsByMockPropertyName): bool
    {
        foreach ($usingTestMethodsByMockPropertyName as $usingTestMethods) {
            if (count($usingTestMethods) !== 0) {
                return \true;
            }
        }
        return \false;
    }
    private function isMissingExpectsOnMockObjectMethodCallInSetUp(Class_ $class): bool
    {
        $setupClassMethod = $class->getMethod(MethodName::SET_UP);
        if (!$setupClassMethod instanceof ClassMethod) {
            return \false;
        }
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstancesOfScoped((array) $setupClassMethod->stmts, MethodCall::class);
        foreach ($methodCalls as $methodCall) {
            if (!$this->isName($methodCall->name, 'method')) {
                continue;
            }
            $type = $this->getType($methodCall->var);
            if (!$type instanceof NeverType && !$this->isObjectType($methodCall->var, new ObjectType(PHPUnitClassName::MOCK_OBJECT))) {
                continue;
            }
            if ($methodCall->var instanceof Variable || $methodCall->var instanceof PropertyFetch) {
                return \true;
            }
        }
        return \false;
    }
}
