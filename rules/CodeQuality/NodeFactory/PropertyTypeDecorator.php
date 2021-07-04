<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class PropertyTypeDecorator
{
    public function __construct(
        private PhpVersionProvider $phpVersionProvider,
        private StaticTypeMapper $staticTypeMapper,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private PhpDocInfoFactory $phpDocInfoFactory
    ) {
    }

    public function decorateProperty(Property $property, Type $propertyType): void
    {
        $this->decoratePropertyWithVarDoc($property, $propertyType);
        $this->decoratePropertyWithType($property, $propertyType);
    }

    private function decoratePropertyWithVarDoc(Property $property, Type $propertyType): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $phpDocInfo->makeMultiLined();

        if ($this->isNonMixedArrayType($propertyType)) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $propertyType);
            $property->type = new Identifier('array');
            return;
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            $phpParserNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
                $propertyType,
                TypeKind::PROPERTY()
            );
            if (! $phpParserNode instanceof Node) {
                // fallback to doc type in PHP 7.4
                $this->phpDocTypeChanger->changeVarType($phpDocInfo, $propertyType);
            }
        } else {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $propertyType);
        }
    }

    private function decoratePropertyWithType(Property $property, Type $propertyType): void
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return;
        }

        $phpParserNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY());
        if (! $phpParserNode instanceof Node) {
            return;
        }

        $property->type = $phpParserNode;
    }

    private function isNonMixedArrayType(Type $type): bool
    {
        if (! $type instanceof ArrayType) {
            return false;
        }

        if ($type->getKeyType() instanceof MixedType) {
            return false;
        }

        return ! $type->getItemType() instanceof MixedType;
    }
}
