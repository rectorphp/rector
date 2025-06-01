<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\UnionType;
use RectorPrefix202506\PHPUnit\Framework\MockObject\MockObject;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\SingleMockPropertyTypeRector\SingleMockPropertyTypeRectorTest
 */
final class SingleMockPropertyTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make properties in tests with intersection mock object either object type or mock type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class MockingEntity extends TestCase
{
    private SimpleObject|MockObject $someEntityMock;

    protected function setUp(): void
    {
        $this->someEntityMock = $this->createMock(SimpleObject::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class MockingEntity extends TestCase
{
    private MockObject $someEntityMock;

    protected function setUp(): void
    {
        $this->someEntityMock = $this->createMock(SimpleObject::class);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if (!$property->type instanceof IntersectionType && !$property->type instanceof UnionType) {
                continue;
            }
            $complexType = $property->type;
            if (\count($complexType->types) !== 2) {
                continue;
            }
            foreach ($complexType->types as $intersectionType) {
                if ($this->isName($intersectionType, MockObject::class)) {
                    $property->type = $intersectionType;
                    $hasChanged = \true;
                    break;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
