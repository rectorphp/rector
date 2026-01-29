<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\VarLikeIdentifier;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\MockObjectPropertyDetector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\SuffixMockObjectPropertyRector\SuffixMockObjectPropertyRectorTest
 */
final class SuffixMockObjectPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private MockObjectPropertyDetector $mockObjectPropertyDetector;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, MockObjectPropertyDetector $mockObjectPropertyDetector)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->mockObjectPropertyDetector = $mockObjectPropertyDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Suffix mock object property names with "Mock" to clearly separate from real objects later on', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class MockingEntity extends TestCase
{
    private MockObject $simpleObject;

    protected function setUp(): void
    {
        $this->simpleObject = $this->createMock(SimpleObject::class);
    }

    public function test()
    {
        $this->simpleObject->method('someMethod')->willReturn('someValue');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class MockingEntity extends TestCase
{
    private MockObject $simpleObjectMock;

    protected function setUp(): void
    {
        $this->simpleObjectMock = $this->createMock(SimpleObject::class);
    }

    public function test()
    {
        $this->simpleObjectMock->method('someMethod')->willReturn('someValue');
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
    public function refactor(Node $node): ?Class_
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$this->mockObjectPropertyDetector->detect($property)) {
                continue;
            }
            $propertyName = $this->getName($property);
            if (substr_compare($propertyName, 'Mock', -strlen('Mock')) === 0) {
                continue;
            }
            $newPropertyName = $propertyName . 'Mock';
            $property->props[0]->name = new VarLikeIdentifier($newPropertyName);
            $this->renamePropertyUsagesInClass($node, $propertyName, $newPropertyName);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function renamePropertyUsagesInClass(Class_ $class, string $oldPropertyName, string $newPropertyName): void
    {
        $this->traverseNodesWithCallable($class, function (Node $node) use ($oldPropertyName, $newPropertyName): ?Node {
            if (!$node instanceof PropertyFetch) {
                return null;
            }
            // is local property?
            if (!$node->var instanceof Variable && !$this->isName($node->var, 'this')) {
                return null;
            }
            if (!$this->isName($node->name, $oldPropertyName)) {
                return null;
            }
            $node->name = new Identifier($newPropertyName);
            return $node;
        });
    }
}
