<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\ChangeMockObjectReturnUnionToIntersectionRector\ChangeMockObjectReturnUnionToIntersectionRectorTest
 */
final class ChangeMockObjectReturnUnionToIntersectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return null;
        }
        $returnTypeNode = $returnTagValueNode->type;
        if (!$returnTypeNode instanceof UnionTypeNode) {
            return null;
        }
        // must contain a MockObject or Stub member to be a mock union
        if (!$this->hasMockObjectOrStubType($returnTypeNode)) {
            return null;
        }
        $bracketsAwareIntersectionTypeNode = new BracketsAwareIntersectionTypeNode($returnTypeNode->types);
        $this->phpDocTypeChanger->changeReturnTypeNode($node, $phpDocInfo, $bracketsAwareIntersectionTypeNode);
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change a MockObject @return union docblock to an intersection type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @return Event|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createEvent(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(Event::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @return Event&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createEvent(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(Event::class);
    }
}
CODE_SAMPLE
)]);
    }
    private function hasMockObjectOrStubType(UnionTypeNode $unionTypeNode): bool
    {
        $found = \false;
        foreach ($unionTypeNode->types as $typeNode) {
            if ($this->isMockObjectOrStubType($typeNode)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    private function isMockObjectOrStubType(TypeNode $typeNode): bool
    {
        if (!$typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        $typeName = ltrim($typeNode->name, '\\');
        return in_array($typeName, [PHPUnitClassName::MOCK_OBJECT, 'MockObject', PHPUnitClassName::STUB, 'Stub'], \true);
    }
}
