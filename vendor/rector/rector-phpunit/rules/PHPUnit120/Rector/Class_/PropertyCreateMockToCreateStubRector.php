<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\MockObjectExprDetector;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\MockObjectPropertyDetector;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\Class_\PropertyCreateMockToCreateStubRector\PropertyCreateMockToCreateStubRectorTest
 *
 * @see https://github.com/sebastianbergmann/phpunit/commit/24c208d6a340c3071f28a9b5cce02b9377adfd43
 */
final class PropertyCreateMockToCreateStubRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private MockObjectExprDetector $mockObjectExprDetector;
    /**
     * @readonly
     */
    private MockObjectPropertyDetector $mockObjectPropertyDetector;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, MockObjectExprDetector $mockObjectExprDetector, MockObjectPropertyDetector $mockObjectPropertyDetector)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->mockObjectExprDetector = $mockObjectExprDetector;
        $this->mockObjectPropertyDetector = $mockObjectPropertyDetector;
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
        /** @var ClassMethod $setUpClassMethod */
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        $propertyNamesToCreateMockMethodCalls = $this->mockObjectPropertyDetector->collectFromClassMethod($setUpClassMethod);
        if ($propertyNamesToCreateMockMethodCalls === []) {
            return null;
        }
        $hasChanged = \false;
        // find property fetch usage, is it exnted with method expectaitions?
        foreach ($propertyNamesToCreateMockMethodCalls as $propertyName => $createMockMethodCall) {
            if ($this->mockObjectExprDetector->isPropertyUsedForMocking($node, $propertyName)) {
                continue;
            }
            $createMockMethodCall->name = new Identifier('createStub');
            $hasChanged = \true;
            // update property type
            $property = $node->getProperty($propertyName);
            /** @var Property $property */
            $property->type = new FullyQualified(PHPUnitClassName::STUB);
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change mock object property that is never mocked to createStub()', [new CodeSample(<<<'CODE_SAMPLE'
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
        $someObject = new SomeClass($this->someServiceMock);
    }

    public function testTwo(): void
    {
        $someObject = new AnotherClass($this->someServiceMock);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\Stub\Stub $someServiceMock;

    protected function setUp(): void
    {
        $this->someServiceMock = $this->createStub(SomeService::class);
    }

    public function testOne(): void
    {
        $someObject = new SomeClass($this->someServiceMock);
    }

    public function testTwo(): void
    {
        $someObject = new AnotherClass($this->someServiceMock);
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
        $setUpClassMethod = $class->getMethod(MethodName::SET_UP);
        // the setup class method must be here, so we have a place where the createMock() is used
        return !$setUpClassMethod instanceof ClassMethod;
    }
}
