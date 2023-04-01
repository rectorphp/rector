<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Mapper;

use RectorPrefix202304\Nette\Utils\Strings;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
final class ScalarStringToTypeMapper
{
    /**
     * @var array<class-string<Type>, string[]>
     */
    private const SCALAR_NAME_BY_TYPE = [StringType::class => ['string'], AccessoryNonEmptyStringType::class => ['non-empty-string'], ClassStringType::class => ['class-string'], FloatType::class => ['float', 'real', 'double'], IntegerType::class => ['int', 'integer'], BooleanType::class => ['bool', 'boolean'], NullType::class => ['null'], VoidType::class => ['void'], ResourceType::class => ['resource'], CallableType::class => ['callback', 'callable'], ObjectWithoutClassType::class => ['object'], NeverType::class => ['never', 'never-return', 'never-returns', 'no-return']];
    public function mapScalarStringToType(string $scalarName) : Type
    {
        $loweredScalarName = Strings::lower($scalarName);
        if ($loweredScalarName === 'false') {
            return new ConstantBooleanType(\false);
        }
        if ($loweredScalarName === 'true') {
            return new ConstantBooleanType(\true);
        }
        foreach (self::SCALAR_NAME_BY_TYPE as $objectType => $scalarNames) {
            if (!\in_array($loweredScalarName, $scalarNames, \true)) {
                continue;
            }
            return new $objectType();
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
