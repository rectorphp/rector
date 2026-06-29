<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\Property\MockObjectVarToStubRector\MockObjectVarToStubRectorTest
 */
final class MockObjectVarToStubRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getNodeTypes(): array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Property
    {
        // only inside PHPUnit TestCase scope
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        // only properties already converted to a Stub native type
        if (!$this->isStubNativeType($node->type)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        if (!$this->replaceMockObjectWithStub($varTagValueNode)) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Update the @var docblock of a property changed to a Stub native type, from MockObject to Stub', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @var FieldModel|MockObject
 */
private \PHPUnit\Framework\MockObject\Stub $leadFieldModel;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @var FieldModel|Stub
 */
private \PHPUnit\Framework\MockObject\Stub $leadFieldModel;
CODE_SAMPLE
)]);
    }
    private function isStubNativeType(?Node $typeNode): bool
    {
        if ($typeNode instanceof IntersectionType) {
            $found = \false;
            foreach ($typeNode->types as $innerType) {
                if ($this->isStubName($innerType)) {
                    $found = \true;
                    break;
                }
            }
            return $found;
        }
        return $this->isStubName($typeNode);
    }
    private function isStubName(?Node $node): bool
    {
        return $node instanceof Node && $this->getName($node) === PHPUnitClassName::STUB;
    }
    private function replaceMockObjectWithStub(VarTagValueNode $varTagValueNode): bool
    {
        $typeNode = $varTagValueNode->type;
        // fresh nodes (without original token positions) are re-printed, mutating in place is not
        if ($typeNode instanceof UnionTypeNode || $typeNode instanceof IntersectionTypeNode) {
            $hasChanged = \false;
            foreach ($typeNode->types as $key => $innerType) {
                if ($innerType instanceof IdentifierTypeNode && $this->isMockObjectIdentifier($innerType)) {
                    $typeNode->types[$key] = new IdentifierTypeNode($this->resolveStubName($innerType->name));
                    $hasChanged = \true;
                }
            }
            return $hasChanged;
        }
        if ($typeNode instanceof IdentifierTypeNode && $this->isMockObjectIdentifier($typeNode)) {
            $varTagValueNode->type = new IdentifierTypeNode($this->resolveStubName($typeNode->name));
            return \true;
        }
        return \false;
    }
    private function isMockObjectIdentifier(IdentifierTypeNode $identifierTypeNode): bool
    {
        $lastBackslashPosition = strrpos($identifierTypeNode->name, '\\');
        $shortName = $lastBackslashPosition === \false ? $identifierTypeNode->name : (string) substr($identifierTypeNode->name, $lastBackslashPosition + 1);
        return $shortName === 'MockObject';
    }
    private function resolveStubName(string $name): string
    {
        $lastBackslashPosition = strrpos($name, '\\');
        return $lastBackslashPosition === \false ? 'Stub' : substr($name, 0, $lastBackslashPosition + 1) . 'Stub';
    }
}
