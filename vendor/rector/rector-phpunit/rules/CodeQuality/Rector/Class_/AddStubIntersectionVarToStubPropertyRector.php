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
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\AddStubIntersectionVarToStubPropertyRector\AddStubIntersectionVarToStubPropertyRectorTest
 */
final class AddStubIntersectionVarToStubPropertyRector extends AbstractRector
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
        $propertyNamesToCreateStubCalls = $setUpClassMethod instanceof ClassMethod ? $this->mockObjectPropertyDetector->collectFromClassMethod($setUpClassMethod, 'createStub') : [];
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // only properties typed as a native Stub
            if (!$this->mockObjectPropertyDetector->detect($property, PHPUnitClassName::STUB)) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            $varTagValueNode = $phpDocInfo->getVarTagValueNode();
            // already has an intersection @var, skip
            if ($varTagValueNode instanceof VarTagValueNode && $varTagValueNode->type instanceof IntersectionTypeNode) {
                continue;
            }
            $intersectionTypeNode = $this->resolveStubIntersection($property, $propertyNamesToCreateStubCalls, $varTagValueNode);
            if (!$intersectionTypeNode instanceof BracketsAwareIntersectionTypeNode) {
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
        return new RuleDefinition('Add a Stub intersection @var docblock with the stubbed class to a native Stub property', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\Stub $someServiceStub;

    protected function setUp(): void
    {
        $this->someServiceStub = $this->createStub(SomeService::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&\SomeService
     */
    private \PHPUnit\Framework\MockObject\Stub $someServiceStub;

    protected function setUp(): void
    {
        $this->someServiceStub = $this->createStub(SomeService::class);
    }
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @var SomeService
     */
    private \PHPUnit\Framework\MockObject\Stub $someServiceStub;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\Stub&SomeService
     */
    private \PHPUnit\Framework\MockObject\Stub $someServiceStub;
}
CODE_SAMPLE
)]);
    }
    /**
     * @param array<string, MethodCall|StaticCall> $propertyNamesToCreateStubCalls
     */
    private function resolveStubIntersection(Property $property, array $propertyNamesToCreateStubCalls, ?VarTagValueNode $varTagValueNode): ?BracketsAwareIntersectionTypeNode
    {
        $propertyName = $property->props[0]->name->toString();
        // 1. prefer the stubbed class from a setUp() createStub() call
        $createStubCall = $propertyNamesToCreateStubCalls[$propertyName] ?? null;
        if ($createStubCall !== null) {
            $stubbedClass = $this->resolveStubbedClass($createStubCall);
            if ($stubbedClass !== null) {
                return new BracketsAwareIntersectionTypeNode([new IdentifierTypeNode('\\' . PHPUnitClassName::STUB), new IdentifierTypeNode('\\' . $stubbedClass)]);
            }
        }
        // 2. fall back to a bare single-class @var docblock
        if ($varTagValueNode instanceof VarTagValueNode && $varTagValueNode->type instanceof IdentifierTypeNode) {
            // skip Stub/MockObject themselves, only real stubbed class types
            if (in_array($this->resolveShortName($varTagValueNode->type->name), ['Stub', 'MockObject'], \true)) {
                return null;
            }
            return new BracketsAwareIntersectionTypeNode([new IdentifierTypeNode('\\' . PHPUnitClassName::STUB), $varTagValueNode->type]);
        }
        return null;
    }
    private function resolveShortName(string $name): string
    {
        $lastBackslashPosition = strrpos($name, '\\');
        return $lastBackslashPosition === \false ? $name : (string) substr($name, $lastBackslashPosition + 1);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $createStubCall
     */
    private function resolveStubbedClass($createStubCall): ?string
    {
        $firstArg = $createStubCall->getArgs()[0] ?? null;
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
