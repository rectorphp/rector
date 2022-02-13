<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;
final class PhpDocTypeChanger
{
    /**
     * @var array<class-string<Node>>
     */
    public const ALLOWED_TYPES = [\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode::class, \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode::class, \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode::class, \PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode::class];
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory
     */
    private $paramPhpDocNodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Comment\CommentsMerger
     */
    private $commentsMerger;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator, \Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory $paramPhpDocNodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\Comment\CommentsMerger $commentsMerger, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
        $this->paramPhpDocNodeFactory = $paramPhpDocNodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->commentsMerger = $commentsMerger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function changeVarType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\Type\Type $newType) : void
    {
        // better skip, could crash hard
        if ($phpDocInfo->hasInvalidTag('@var')) {
            return;
        }
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEqual($phpDocInfo->getVarType(), $newType)) {
            return;
        }
        // prevent existing type override by mixed
        if (!$phpDocInfo->getVarType() instanceof \PHPStan\Type\MixedType && $newType instanceof \PHPStan\Type\Constant\ConstantArrayType && $newType->getItemType() instanceof \PHPStan\Type\NeverType) {
            return;
        }
        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
        $currentVarTagValueNode = $phpDocInfo->getVarTagValueNode();
        if ($currentVarTagValueNode !== null) {
            // only change type
            $currentVarTagValueNode->type = $newPHPStanPhpDocType;
        } else {
            // add completely new one
            $varTagValueNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode($newPHPStanPhpDocType, '', '');
            $phpDocInfo->addTagValueNode($varTagValueNode);
        }
    }
    public function changeReturnType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\Type\Type $newType) : bool
    {
        // better not touch this, can crash
        if ($phpDocInfo->hasInvalidTag('@return')) {
            return \false;
        }
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEqual($phpDocInfo->getReturnType(), $newType)) {
            return \false;
        }
        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
        $currentReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if ($currentReturnTagValueNode !== null) {
            // only change type
            $currentReturnTagValueNode->type = $newPHPStanPhpDocType;
        } else {
            // add completely new one
            $returnTagValueNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode($newPHPStanPhpDocType, '');
            $phpDocInfo->addTagValueNode($returnTagValueNode);
        }
        return \true;
    }
    public function changeParamType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\Type\Type $newType, \PhpParser\Node\Param $param, string $paramName) : void
    {
        // better skip, could crash hard
        if ($phpDocInfo->hasInvalidTag('@param')) {
            return;
        }
        $phpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
        // override existing type
        if ($paramTagValueNode !== null) {
            // already set
            $currentType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($paramTagValueNode->type, $param);
            // avoid overriding better type
            if ($this->typeComparator->isSubtype($currentType, $newType)) {
                return;
            }
            if ($this->typeComparator->areTypesEqual($currentType, $newType)) {
                return;
            }
            $paramTagValueNode->type = $phpDocType;
        } else {
            $paramTagValueNode = $this->paramPhpDocNodeFactory->create($phpDocType, $param);
            $phpDocInfo->addTagValueNode($paramTagValueNode);
        }
    }
    public function isAllowed(\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode) : bool
    {
        if ($typeNode instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
            foreach ($typeNode->types as $type) {
                if ($this->isAllowed($type)) {
                    return \true;
                }
            }
        }
        return \in_array(\get_class($typeNode), self::ALLOWED_TYPES, \true);
    }
    public function copyPropertyDocToParam(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Param $param) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return;
        }
        $varTag = $phpDocInfo->getVarTagValueNode();
        if (!$varTag instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            $this->processKeepComments($property, $param);
            return;
        }
        if ($varTag->description !== '') {
            return;
        }
        $functionLike = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $paramVarName = $this->nodeNameResolver->getName($param->var);
        if (!$functionLike instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        if (!$this->isAllowed($varTag->type)) {
            return;
        }
        if (!\is_string($paramVarName)) {
            return;
        }
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class);
        $param->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        $phpDocInfo = $functionLike->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO);
        $paramType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($varTag, $property);
        $this->changeParamType($phpDocInfo, $paramType, $param, $paramVarName);
        $this->processKeepComments($property, $param);
    }
    public function changeVarTypeNode(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode) : void
    {
        // add completely new one
        $varTagValueNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode($typeNode, '', '');
        $phpDocInfo->addTagValueNode($varTagValueNode);
    }
    private function processKeepComments(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Param $param) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($param);
        $varTag = $phpDocInfo->getVarTagValueNode();
        $toBeRemoved = !$varTag instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
        $this->commentsMerger->keepComments($param, [$property]);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($param);
        $varTag = $phpDocInfo->getVarTagValueNode();
        if (!$toBeRemoved) {
            return;
        }
        if (!$varTag instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return;
        }
        if ($varTag->description !== '') {
            return;
        }
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class);
    }
}
