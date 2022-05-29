<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\Guard\PropertyTypeOverrideGuard;
use Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\PropertyTypeDeclarationRectorTest
 */
final class PropertyTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer
     */
    private $varDocPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Guard\PropertyTypeOverrideGuard
     */
    private $propertyTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer $varDocPropertyTypeInferer, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\TypeDeclaration\Guard\PropertyTypeOverrideGuard $propertyTypeOverrideGuard, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->varDocPropertyTypeInferer = $varDocPropertyTypeInferer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->propertyTypeOverrideGuard = $propertyTypeOverrideGuard;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add @var to properties that are missing it', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $value;

    public function run()
    {
        $this->value = 123;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int
     */
    private $value;

    public function run()
    {
        $this->value = 123;
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
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (\count($node->props) !== 1) {
            return null;
        }
        if ($node->type !== null) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($phpDocInfo->hasInheritDoc() && !$node->isPrivate()) {
            return null;
        }
        if ($this->isVarDocAlreadySet($phpDocInfo)) {
            return null;
        }
        $type = $this->varDocPropertyTypeInferer->inferProperty($node);
        if ($type instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        if (!$node->isPrivate() && $type instanceof \PHPStan\Type\NullType) {
            return null;
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES) && $this->propertyTypeOverrideGuard->isLegal($node)) {
            $this->completeTypedProperty($type, $node);
            return $node;
        }
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
        return $node;
    }
    private function isVarDocAlreadySet(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        foreach (['@var', '@phpstan-var', '@psalm-var'] as $tagName) {
            $varType = $phpDocInfo->getVarType($tagName);
            if (!$varType instanceof \PHPStan\Type\MixedType) {
                return \true;
            }
        }
        return \false;
    }
    private function completeTypedProperty(\PHPStan\Type\Type $type, \PhpParser\Node\Stmt\Property $property) : void
    {
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY);
        if ($propertyTypeNode === null) {
            return;
        }
        if ($propertyTypeNode instanceof \PhpParser\Node\UnionType) {
            if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::UNION_TYPES)) {
                $property->type = $propertyTypeNode;
                return;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $type);
            return;
        }
        $property->type = $propertyTypeNode;
    }
}
