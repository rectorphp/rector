<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Enum\ClassName;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\TrustedClassMethodPropertyTypeInferer;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector\TypedPropertyFromStrictSetUpRectorTest
 */
final class TypedPropertyFromStrictSetUpRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private TrustedClassMethodPropertyTypeInferer $trustedClassMethodPropertyTypeInferer;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(TrustedClassMethodPropertyTypeInferer $trustedClassMethodPropertyTypeInferer, StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater)
    {
        $this->trustedClassMethodPropertyTypeInferer = $trustedClassMethodPropertyTypeInferer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict typed property based on setUp() strict typed assigns in TestCase', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    private $value;

    public function setUp()
    {
        $this->value = 1000;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    private int $value;

    public function setUp()
    {
        $this->value = 1000;
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
    public function refactor(Node $node) : ?Node
    {
        $setUpClassMethod = $node->getMethod(MethodName::SET_UP);
        if (!$setUpClassMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // type is already set
            if ($property->type !== null) {
                continue;
            }
            // is not private? we cannot be sure about other usage
            if (!$property->isPrivate()) {
                continue;
            }
            $propertyType = $this->trustedClassMethodPropertyTypeInferer->inferProperty($node, $property, $setUpClassMethod);
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
            if (!$propertyTypeNode instanceof Node) {
                continue;
            }
            if ($propertyType instanceof ObjectType && $propertyType->getClassName() === ClassName::MOCK_OBJECT) {
                $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
                $varTag = $phpDocInfo->getVarTagValueNode();
                $varType = $phpDocInfo->getVarType();
                if ($varTag instanceof VarTagValueNode && $varType instanceof ObjectType && $varType->getClassName() !== ClassName::MOCK_OBJECT) {
                    $varTag->type = new IntersectionTypeNode([new FullyQualifiedIdentifierTypeNode($propertyType->getClassName()), new FullyQualifiedIdentifierTypeNode($varType->getClassName())]);
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($property);
                }
            }
            $property->type = $propertyTypeNode;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
