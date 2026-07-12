<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Mapper;

use RectorPrefix202607\Nette\Utils\Strings;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\Accessory\AccessoryLiteralStringType;
use PHPStan\Type\Accessory\AccessoryLowercaseStringType;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\Accessory\AccessoryNonFalsyStringType;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Accessory\AccessoryUppercaseStringType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
final class ScalarStringToTypeMapper
{
    /**
     * @var array<class-string<Type>, string[]>
     */
    private const SCALAR_NAME_BY_TYPE = [StringType::class => ['string'], AccessoryNonEmptyStringType::class => ['non-empty-string'], AccessoryLiteralStringType::class => ['literal-string'], AccessoryNumericStringType::class => ['numeric-string'], AccessoryNonFalsyStringType::class => ['non-falsy-string', 'truthy-string'], AccessoryLowercaseStringType::class => ['lowercase-string'], AccessoryUppercaseStringType::class => ['uppercase-string'], NonEmptyArrayType::class => ['non-empty-array'], ClassStringType::class => ['class-string'], FloatType::class => ['float', 'real', 'double'], IntegerType::class => ['int', 'integer'], BooleanType::class => ['bool', 'boolean'], NullType::class => ['null'], VoidType::class => ['void'], ResourceType::class => ['resource'], CallableType::class => ['callback', 'callable'], ObjectWithoutClassType::class => ['object'], NeverType::class => ['never', 'never-return', 'never-returns', 'no-return']];
    public function mapScalarStringToType(string $scalarName): Type
    {
        $loweredScalarName = Strings::lower($scalarName);
        if ($loweredScalarName === 'false') {
            return new ConstantBooleanType(\false);
        }
        if ($loweredScalarName === 'true') {
            return new ConstantBooleanType(\true);
        }
        if ($loweredScalarName === 'positive-int') {
            return IntegerRangeType::createAllGreaterThan(0);
        }
        if ($loweredScalarName === 'negative-int') {
            return IntegerRangeType::createAllSmallerThan(0);
        }
        if ($loweredScalarName === 'non-negative-int') {
            return IntegerRangeType::fromInterval(0, null);
        }
        if ($loweredScalarName === 'non-positive-int') {
            return IntegerRangeType::fromInterval(null, 0);
        }
        if ($loweredScalarName === 'array-key') {
            return new UnionType([new IntegerType(), new StringType()]);
        }
        foreach (self::SCALAR_NAME_BY_TYPE as $objectType => $scalarNames) {
            if (!in_array($loweredScalarName, $scalarNames, \true)) {
                continue;
            }
            return new $objectType();
        }
        if ($loweredScalarName === 'list') {
            return TypeCombinator::intersect(new ArrayType(new MixedType(), new MixedType()), new AccessoryArrayListType());
        }
        if ($loweredScalarName === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }
        if ($loweredScalarName === 'iterable') {
            return new IterableType(new MixedType(), new MixedType());
        }
        if ($loweredScalarName === 'mixed') {
            return new MixedType(\true);
        }
        return new MixedType();
    }
}
