<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;

final class PhpDocTypeChanger
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper,
        private TypeComparator $typeComparator,
        private ParamPhpDocNodeFactory $paramPhpDocNodeFactory
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
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);

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
            TypeKind::KIND_RETURN
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

        $phpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
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
}
