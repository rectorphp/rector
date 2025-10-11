<?php

declare (strict_types=1);
namespace Rector\Privatization\TypeManipulator;

use PHPStan\Type\Accessory\AccessoryLiteralStringType;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\Accessory\AccessoryNonFalsyStringType;
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
use Rector\NodeTypeResolver\PHPStan\TypeHasher;
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
     * @readonly
     */
    private TypeHasher $typeHasher;
    /**
     * @var int
     */
    private const MAX_PRINTED_UNION_DOC_LENGHT = 77;
    public function __construct(TypeFactory $typeFactory, StaticTypeMapper $staticTypeMapper, \Rector\Privatization\TypeManipulator\ArrayTypeLeastCommonDenominatorResolver $arrayTypeLeastCommonDenominatorResolver, TypeHasher $typeHasher)
    {
        $this->typeFactory = $typeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->arrayTypeLeastCommonDenominatorResolver = $arrayTypeLeastCommonDenominatorResolver;
        $this->typeHasher = $typeHasher;
    }
    /**
     * Generalize false/true constantArrayType to bool,
     * as mostly default value but accepts both
     */
    public function generalizeConstantTypes(Type $type): Type
    {
        $deep = 0;
        return TypeTraverser::map($type, function (Type $type, callable $traverseCallback) use (&$deep): Type {
            ++$deep;
            if ($type instanceof AccessoryNonFalsyStringType || $type instanceof AccessoryLiteralStringType || $type instanceof AccessoryNonEmptyStringType) {
                return new StringType();
            }
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
                    $keyType = $deep === 1 ? new MixedType() : new IntegerType();
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
                    $generalizedUnionedType = $this->generalizeConstantTypes($unionedType);
                    if ($generalizedUnionedType instanceof ArrayType) {
                        $keyType = $this->typeHasher->createTypeHash($generalizedUnionedType->getKeyType());
                        foreach ($generalizedUnionedTypes as $key => $existingUnionedType) {
                            if (!$existingUnionedType instanceof ArrayType) {
                                continue;
                            }
                            $existingKeyType = $this->typeHasher->createTypeHash($existingUnionedType->getKeyType());
                            if ($keyType !== $existingKeyType) {
                                continue;
                            }
                            $uniqueTypes = $this->typeFactory->uniquateTypes([$existingUnionedType->getItemType(), $generalizedUnionedType->getItemType()]);
                            if (count($uniqueTypes) !== 1) {
                                continue;
                            }
                            $generalizedUnionedTypes[$key] = new ArrayType($existingUnionedType->getKeyType(), $uniqueTypes[0]);
                            continue 2;
                        }
                    }
                    $generalizedUnionedTypes[] = $generalizedUnionedType;
                }
                $uniqueGeneralizedUnionTypes = $this->typeFactory->uniquateTypes($generalizedUnionedTypes);
                if (count($uniqueGeneralizedUnionTypes) > 1) {
                    $generalizedUnionType = new UnionType($uniqueGeneralizedUnionTypes);
                    // avoid too huge print in docblock
                    $unionedDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($generalizedUnionType);
                    // too long
                    if (strlen((string) $unionedDocType) > self::MAX_PRINTED_UNION_DOC_LENGHT && $this->avoidPrintedDocblockTrimming($generalizedUnionType) === \false) {
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
    /**
     * Is object only? avoid trimming, as auto import handles it better
     */
    private function avoidPrintedDocblockTrimming(UnionType $unionType): bool
    {
        if ($unionType->getConstantScalarTypes() !== []) {
            return \false;
        }
        if ($unionType->getConstantArrays() !== []) {
            return \false;
        }
        return $unionType->getObjectClassNames() !== [];
    }
}
