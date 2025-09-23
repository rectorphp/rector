<?php

declare (strict_types=1);
namespace Rector\Privatization\TypeManipulator;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class TypeNormalizer
{
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private \Rector\Privatization\TypeManipulator\ArrayTypeLeastCommonDenominatorResolver $arrayTypeLeastCommonDenominatorResolver;
    /**
     * @var int
     */
    private const MAX_PRINTED_UNION_DOC_LENGHT = 77;
    public function __construct(TypeFactory $typeFactory, StaticTypeMapper $staticTypeMapper, \Rector\Privatization\TypeManipulator\ArrayTypeLeastCommonDenominatorResolver $arrayTypeLeastCommonDenominatorResolver)
    {
        $this->typeFactory = $typeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->arrayTypeLeastCommonDenominatorResolver = $arrayTypeLeastCommonDenominatorResolver;
    }
    /**
     * @deprecated This method is deprecated and will be removed in the next major release.
     * Use @see generalizeConstantTypes() instead.
     */
    public function generalizeConstantBoolTypes(Type $type): Type
    {
        return $this->generalizeConstantTypes($type);
    }
    /**
     * Generalize false/true constantArrayType to bool,
     * as mostly default value but accepts both
     */
    public function generalizeConstantTypes(Type $type): Type
    {
        return TypeTraverser::map($type, function (Type $type, callable $traverseCallback): Type {
            if ($type instanceof ConstantBooleanType) {
                return new BooleanType();
            }
            if ($type instanceof ConstantStringType) {
                return new StringType();
            }
            if ($type instanceof ConstantFloatType) {
                return new FloatType();
            }
            if ($type instanceof ConstantIntegerType) {
                return new IntegerType();
            }
            if ($type instanceof ConstantArrayType) {
                // is relevant int constantArrayType?
                if ($this->isImplicitNumberedListKeyType($type)) {
                    $keyType = new MixedType();
                } else {
                    $keyType = $this->generalizeConstantTypes($type->getKeyType());
                }
                // should be string[]
                $itemType = $traverseCallback($type->getItemType(), $traverseCallback);
                if ($itemType instanceof ConstantStringType) {
                    $itemType = new StringType();
                }
                if ($itemType instanceof ConstantArrayType) {
                    $itemType = $this->generalizeConstantTypes($itemType);
                }
                return new ArrayType($keyType, $itemType);
            }
            if ($type instanceof UnionType) {
                $generalizedUnionedTypes = [];
                foreach ($type->getTypes() as $unionedType) {
                    $generalizedUnionedTypes[] = $this->generalizeConstantTypes($unionedType);
                }
                $uniqueGeneralizedUnionTypes = $this->typeFactory->uniquateTypes($generalizedUnionedTypes);
                if (count($uniqueGeneralizedUnionTypes) > 1) {
                    $generalizedUnionType = new UnionType($uniqueGeneralizedUnionTypes);
                    // avoid too huge print in docblock
                    $unionedDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($generalizedUnionType);
                    // too long
                    if (strlen((string) $unionedDocType) > self::MAX_PRINTED_UNION_DOC_LENGHT) {
                        $alwaysKnownArrayType = $this->narrowToAlwaysKnownArrayType($generalizedUnionType);
                        if ($alwaysKnownArrayType instanceof ArrayType) {
                            return $alwaysKnownArrayType;
                        }
                        return new MixedType();
                    }
                    return $generalizedUnionType;
                }
                return $uniqueGeneralizedUnionTypes[0];
            }
            $convertedType = $traverseCallback($type, $traverseCallback);
            if ($convertedType instanceof NeverType) {
                return new MixedType();
            }
            return $convertedType;
        });
    }
    private function isImplicitNumberedListKeyType(ConstantArrayType $constantArrayType): bool
    {
        if (!$constantArrayType->getKeyType() instanceof UnionType) {
            return \false;
        }
        foreach ($constantArrayType->getKeyType()->getTypes() as $key => $keyType) {
            if ($keyType instanceof ConstantIntegerType) {
                if ($keyType->getValue() === $key) {
                    continue;
                }
                return \false;
            }
            return \false;
        }
        return \true;
    }
    private function narrowToAlwaysKnownArrayType(UnionType $unionType): ?ArrayType
    {
        // always an array?
        if (count($unionType->getArrays()) !== count($unionType->getTypes())) {
            return null;
        }
        $arrayUniqueKeyType = $this->arrayTypeLeastCommonDenominatorResolver->sharedArrayStructure(...$unionType->getTypes());
        return new ArrayType($arrayUniqueKeyType, new MixedType());
    }
}
