<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node\Param;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;

final class PhpDocTypeChanger
{
    /**
     * @var TypeComparator
     */
    private $typeComparator;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var ParamPhpDocNodeFactory
     */
    private $paramPhpDocNodeFactory;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    public function __construct(
        ParamPhpDocNodeFactory $paramPhpDocNodeFactory,
        StaticTypeMapper $staticTypeMapper,
        TypeComparator $typeComparator,
        RectorChangeCollector $rectorChangeCollector,
        CurrentNodeProvider $currentNodeProvider
    ) {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->paramPhpDocNodeFactory = $paramPhpDocNodeFactory;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->currentNodeProvider = $currentNodeProvider;
    }

    public function changeVarType(PhpDocInfo $phpDocInfo, Type $newType): void
    {
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEquals($phpDocInfo->getVarType(), $newType)) {
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
            $attributeAwareVarTagValueNode = new AttributeAwareVarTagValueNode($newPHPStanPhpDocType, '', '');
            $phpDocInfo->addTagValueNode($attributeAwareVarTagValueNode);
        }

        // notify about node change
        $this->notifyChange();
    }

    public function changeReturnType(PhpDocInfo $phpDocInfo, Type $newType): void
    {
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEquals($phpDocInfo->getReturnType(), $newType)) {
            return;
        }

        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
        $currentReturnTagValueNode = $phpDocInfo->getReturnTagValue();

        if ($currentReturnTagValueNode !== null) {
            // only change type
            $currentReturnTagValueNode->type = $newPHPStanPhpDocType;
        } else {
            // add completely new one
            $attributeAwareReturnTagValueNode = new AttributeAwareReturnTagValueNode($newPHPStanPhpDocType, '');
            $phpDocInfo->addTagValueNode($attributeAwareReturnTagValueNode);
        }

        // notify about node change
        $this->notifyChange();
    }

    public function changeParamType(PhpDocInfo $phpDocInfo, Type $newType, Param $param, string $paramName): void
    {
        $phpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);

        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);

        // override existing type
        if ($paramTagValueNode !== null) {
            // already set
            $currentType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
                $paramTagValueNode->type,
                $param
            );

            if ($this->typeComparator->areTypesEquals($currentType, $newType)) {
                return;
            }

            $paramTagValueNode->type = $phpDocType;
        } else {
            $paramTagValueNode = $this->paramPhpDocNodeFactory->create($phpDocType, $param);
            $phpDocInfo->addTagValueNode($paramTagValueNode);
        }

        // notify about node change
        $this->notifyChange();
    }

    private function notifyChange(): void
    {
        $node = $this->currentNodeProvider->getNode();
        if ($node === null) {
            throw new ShouldNotHappenException();
        }

        $this->rectorChangeCollector->notifyNodeFileInfo($node);
    }
}
