<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\PHPUnit\CodeQuality\NodeAnalyser\MockObjectPropertyDetector;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\AddIntersectionVarToMockObjectPropertyRector\AddIntersectionVarToMockObjectPropertyRectorTest
 */
final class AddIntersectionVarToMockObjectPropertyRector extends AbstractRector
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
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, MockObjectPropertyDetector $mockObjectPropertyDetector, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->mockObjectPropertyDetector = $mockObjectPropertyDetector;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
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
        $hasChanged = \false;
        foreach ($propertyNamesToCreateMockMethodCalls as $propertyName => $createMockMethodCall) {
            $property = $node->getProperty($propertyName);
            if (!$property instanceof Property) {
                continue;
            }
            // only properties typed as a bare native MockObject
            if (!$this->mockObjectPropertyDetector->detect($property)) {
                continue;
            }
            $mockedClass = $this->resolveMockedClass($createMockMethodCall);
            if ($mockedClass === null) {
                continue;
            }
            $intersectionTypeNode = new BracketsAwareIntersectionTypeNode([new IdentifierTypeNode('\\' . PHPUnitClassName::MOCK_OBJECT), new IdentifierTypeNode('\\' . $mockedClass)]);
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            // already has an intersection @var, skip
            $varTagValueNode = $phpDocInfo->getVarTagValueNode();
            if ($varTagValueNode instanceof VarTagValueNode && $varTagValueNode->type instanceof IntersectionTypeNode) {
                continue;
            }
            $this->phpDocTypeChanger->changeVarTypeNode($property, $phpDocInfo, $intersectionTypeNode);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add a MockObject intersection @var docblock with the mocked class to a native MockObject property', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $someServiceMock;

    protected function setUp(): void
    {
        $this->someServiceMock = $this->createMock(SomeService::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\SomeService
     */
    private \PHPUnit\Framework\MockObject\MockObject $someServiceMock;

    protected function setUp(): void
    {
        $this->someServiceMock = $this->createMock(SomeService::class);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $createMockCall
     */
    private function resolveMockedClass($createMockCall): ?string
    {
        $firstArg = $createMockCall->getArgs()[0] ?? null;
        if ($firstArg === null) {
            return null;
        }
        if (!$firstArg->value instanceof ClassConstFetch) {
            return null;
        }
        $className = $this->getName($firstArg->value->class);
        if (!is_string($className)) {
            return null;
        }
        return $className;
    }
}
