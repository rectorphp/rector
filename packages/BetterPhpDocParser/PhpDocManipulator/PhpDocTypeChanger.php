<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;
final class PhpDocTypeChanger
{
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @var \Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory
     */
    private $paramPhpDocNodeFactory;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator, \Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory $paramPhpDocNodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
        $this->paramPhpDocNodeFactory = $paramPhpDocNodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
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
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::PROPERTY());
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
    public function changeReturnType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\Type\Type $newType) : void
    {
        // better not touch this, can crash
        if ($phpDocInfo->hasInvalidTag('@return')) {
            return;
        }
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEqual($phpDocInfo->getReturnType(), $newType)) {
            return;
        }
        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::RETURN());
        $currentReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if ($currentReturnTagValueNode !== null) {
            // only change type
            $currentReturnTagValueNode->type = $newPHPStanPhpDocType;
        } else {
            // add completely new one
            $returnTagValueNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode($newPHPStanPhpDocType, '');
            $phpDocInfo->addTagValueNode($returnTagValueNode);
        }
    }
    public function changeParamType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PHPStan\Type\Type $newType, \PhpParser\Node\Param $param, string $paramName) : void
    {
        // better skip, could crash hard
        if ($phpDocInfo->hasInvalidTag('@param')) {
            return;
        }
        $phpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::PARAM());
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
    public function copyPropertyDocToParam(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Param $param) : void
    {
        $phpDocInfo = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO);
        if (!$phpDocInfo) {
            return;
        }
        $varTag = $phpDocInfo->getVarTagValueNode();
        if (!$varTag instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return;
        }
        if ($varTag->description !== '') {
            return;
        }
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class);
        $param->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        $functionLike = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $paramVarName = $this->nodeNameResolver->getName($param->var);
        if ($functionLike instanceof \PhpParser\Node\Stmt\ClassMethod && $varTag->type instanceof \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode && \is_string($paramVarName)) {
            $phpDocInfo = $functionLike->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PHP_DOC_INFO);
            $paramType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($varTag, $property);
            $this->changeParamType($phpDocInfo, $paramType, $param, $paramVarName);
        }
    }
}
