<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\Enum\ClassName;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassLike\RemoveTypedPropertyNonMockDocblockRector\RemoveTypedPropertyNonMockDocblockRectorTest
 */
final class RemoveTypedPropertyNonMockDocblockRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private VarTagRemover $varTagRemover;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(VarTagRemover $varTagRemover, StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->varTagRemover = $varTagRemover;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove @var annotation for PHPUnit\\Framework\\MockObject\\MockObject combined with native object type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    /**
     * @var SomeClass|MockObject
     */
    private SomeClass $someProperty;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    private SomeClass $someProperty;
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType(ClassName::TEST_CASE_CLASS))) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // not yet typed
            if (!$property->type instanceof Node) {
                continue;
            }
            if (\count($property->props) !== 1) {
                continue;
            }
            if (!$property->type instanceof FullyQualified) {
                continue;
            }
            if ($this->isObjectType($property->type, new ObjectType(ClassName::MOCK_OBJECT))) {
                continue;
            }
            $propertyDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if (!$this->isVarTagUnionTypeMockObject($propertyDocInfo, $property)) {
                continue;
            }
            // clear var docblock
            if ($this->varTagRemover->removeVarTag($property)) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function isVarTagUnionTypeMockObject(PhpDocInfo $phpDocInfo, Property $property) : bool
    {
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return \false;
        }
        if (!$varTagValueNode->type instanceof UnionTypeNode) {
            return \false;
        }
        $varTagType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
        if (!$varTagType instanceof UnionType) {
            return \false;
        }
        foreach ($varTagType->getTypes() as $unionedType) {
            if ($unionedType->isSuperTypeOf(new ObjectType(ClassName::MOCK_OBJECT))->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
