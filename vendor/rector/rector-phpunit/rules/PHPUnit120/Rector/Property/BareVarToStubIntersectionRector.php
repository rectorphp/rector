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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\Property\BareVarToStubIntersectionRector\BareVarToStubIntersectionRectorTest
 */
final class BareVarToStubIntersectionRector extends AbstractRector
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
        if (!$this->addStubIntersection($varTagValueNode)) {
            return null;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add a &Stub intersection to a bare single-class @var docblock of a property changed to a Stub native type', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @var FormBuilderInterface
 */
private \PHPUnit\Framework\MockObject\Stub $formBuilder;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @var FormBuilderInterface&Stub
 */
private \PHPUnit\Framework\MockObject\Stub $formBuilder;
CODE_SAMPLE
)]);
    }
    private function isStubNativeType(?Node $typeNode): bool
    {
        if (!$typeNode instanceof Node) {
            return \false;
        }
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
    private function addStubIntersection(VarTagValueNode $varTagValueNode): bool
    {
        $typeNode = $varTagValueNode->type;
        // only a single bare class type, not already a union/intersection
        if (!$typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        // skip Stub/MockObject themselves, only mocked class types
        if (in_array($this->resolveShortName($typeNode->name), ['Stub', 'MockObject'], \true)) {
            return \false;
        }
        $varTagValueNode->type = new BracketsAwareIntersectionTypeNode([$typeNode, new IdentifierTypeNode('Stub')]);
        return \true;
    }
    private function resolveShortName(string $name): string
    {
        $lastBackslashPosition = strrpos($name, '\\');
        return $lastBackslashPosition === \false ? $name : (string) substr($name, $lastBackslashPosition + 1);
    }
}
