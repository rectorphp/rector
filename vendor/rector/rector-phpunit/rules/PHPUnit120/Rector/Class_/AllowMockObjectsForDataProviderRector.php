<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\MockObjectExprDetector;
use Rector\PHPUnit\Enum\PHPUnitAttribute;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\Class_\AllowMockObjectsForDataProviderRector\AllowMockObjectsForDataProviderRectorTest
 */
final class AllowMockObjectsForDataProviderRector extends AbstractRector
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
    private MockObjectExprDetector $mockObjectExprDetector;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, AttributeFinder $attributeFinder, ReflectionProvider $reflectionProvider, MockObjectExprDetector $mockObjectExprDetector)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->attributeFinder = $attributeFinder;
        $this->reflectionProvider = $reflectionProvider;
        $this->mockObjectExprDetector = $mockObjectExprDetector;
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
        if (!$this->hasDataProviderMethodWithExpectLessMethodMock($node)) {
            return null;
        }
        // add attribute
        $node->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS))]);
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add #[AllowMockObjectsWithoutExpectations] attribute to PHPUnit test classes that have methods with data providers and mock objects without expectations', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    #[DataProvider('someDataProvider')]
    public function test()
    {
        $someMock = $this->createMock(\stdClass::class);
        $someMock->expects('method')->willReturn(true);
    }

    public static function someDataProvider(): iterable
    {
        yield [1];
        yield [2];
        yield [3];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class SomeClass extends TestCase
{
    #[DataProvider('someDataProvider')]
    public function test()
    {
        $someMock = $this->createMock(\stdClass::class);
        $someMock->expects('method')->willReturn(true);
    }

    public static function someDataProvider(): iterable
    {
        yield [1];
        yield [2];
        yield [3];
    }
}
CODE_SAMPLE
)]);
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
        return $this->attributeFinder->hasAttributeByClasses($class, [PHPUnitAttribute::ALLOW_MOCK_OBJECTS_WITHOUT_EXPECTATIONS]);
    }
    private function hasDataProviderMethodWithExpectLessMethodMock(Class_ $class): bool
    {
        // has a test method, that contains a ->method() call without expects()
        // and has a data provider attribute?
        foreach ($class->getMethods() as $classMethod) {
            if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
                continue;
            }
            if (!$this->attributeFinder->hasAttributeByClasses($classMethod, [PHPUnitAttribute::DATA_PROVIDER])) {
                continue;
            }
            // has expects-less mock objects
            if ($this->mockObjectExprDetector->hasMethodCallWithoutExpects($classMethod)) {
                return \true;
            }
        }
        return \false;
    }
}
