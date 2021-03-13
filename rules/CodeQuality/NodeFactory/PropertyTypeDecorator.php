<?php

declare(strict_types=1);

namespace Rector\CodeQuality\NodeFactory;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class PropertyTypeDecorator
{
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        PhpVersionProvider $phpVersionProvider,
        StaticTypeMapper $staticTypeMapper,
        PhpDocTypeChanger $phpDocTypeChanger,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function decorateProperty(Property $property, Type $propertyType): void
    {
        $this->decoratePropertyWithVarDoc($property, $propertyType);
        $this->decoratePropertyWithType($property, $propertyType);
    }

    private function decoratePropertyWithVarDoc(Property $property, Type $propertyType): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        if ($this->isNonMixedArrayType($propertyType)) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $propertyType);
            $property->type = new Identifier('array');
            return;
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            $phpParserNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType);
            if ($phpParserNode === null) {
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

        $phpParserNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType);
        if ($phpParserNode === null) {
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
