<?php

declare(strict_types=1);

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
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;

final class PhpDocTypeChanger
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private TypeComparator $typeComparator,
        private ParamPhpDocNodeFactory $paramPhpDocNodeFactory,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function changeVarType(PhpDocInfo $phpDocInfo, Type $newType): void
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
        if (! $phpDocInfo->getVarType() instanceof MixedType && $newType instanceof ConstantArrayType && $newType->getItemType() instanceof NeverType) {
            return;
        }

        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode(
            $newType,
            TypeKind::PROPERTY()
        );

        $currentVarTagValueNode = $phpDocInfo->getVarTagValueNode();
        if ($currentVarTagValueNode !== null) {
            // only change type
            $currentVarTagValueNode->type = $newPHPStanPhpDocType;
        } else {
            // add completely new one
            $varTagValueNode = new VarTagValueNode($newPHPStanPhpDocType, '', '');
            $phpDocInfo->addTagValueNode($varTagValueNode);
        }
    }

    public function changeReturnType(PhpDocInfo $phpDocInfo, Type $newType): void
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
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode(
            $newType,
            TypeKind::RETURN()
        );

        $currentReturnTagValueNode = $phpDocInfo->getReturnTagValue();

        if ($currentReturnTagValueNode !== null) {
            // only change type
            $currentReturnTagValueNode->type = $newPHPStanPhpDocType;
        } else {
            // add completely new one
            $returnTagValueNode = new ReturnTagValueNode($newPHPStanPhpDocType, '');
            $phpDocInfo->addTagValueNode($returnTagValueNode);
        }
    }

    public function changeParamType(PhpDocInfo $phpDocInfo, Type $newType, Param $param, string $paramName): void
    {
        // better skip, could crash hard
        if ($phpDocInfo->hasInvalidTag('@param')) {
            return;
        }

        $phpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, TypeKind::PARAM());
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);

        // override existing type
        if ($paramTagValueNode !== null) {
            // already set
            $currentType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
                $paramTagValueNode->type,
                $param
            );

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

    public function copyPropertyDocToParam(Property $property, Param $param): void
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return;
        }

        $varTag = $phpDocInfo->getVarTagValueNode();
        if (! $varTag instanceof VarTagValueNode) {
            return;
        }

        if ($varTag->description !== '') {
            return;
        }

        $phpDocInfo->removeByType(VarTagValueNode::class);
        $param->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);

        $functionLike = $param->getAttribute(AttributeKey::PARENT_NODE);
        $paramVarName = $this->nodeNameResolver->getName($param->var);

        if (! $functionLike instanceof ClassMethod) {
            return;
        }

        if (! $varTag->type instanceof GenericTypeNode) {
            return;
        }

        if (! is_string($paramVarName)) {
            return;
        }

        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        $paramType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($varTag, $property);

        $this->changeParamType($phpDocInfo, $paramType, $param, $paramVarName);
    }
}
