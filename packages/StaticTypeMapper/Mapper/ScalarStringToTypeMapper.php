<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\StaticTypeMapper\Mapper;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\CallableType;
use RectorPrefix20220606\PHPStan\Type\ClassStringType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantBooleanType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\IterableType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\NeverType;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectWithoutClassType;
use RectorPrefix20220606\PHPStan\Type\ResourceType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\VoidType;
final class ScalarStringToTypeMapper
{
    /**
     * @var array<class-string<Type>, string[]>
     */
    private const SCALAR_NAME_BY_TYPE = [StringType::class => ['string'], ClassStringType::class => ['class-string'], FloatType::class => ['float', 'real', 'double'], IntegerType::class => ['int', 'integer'], BooleanType::class => ['bool', 'boolean'], NullType::class => ['null'], VoidType::class => ['void'], ResourceType::class => ['resource'], CallableType::class => ['callback', 'callable'], ObjectWithoutClassType::class => ['object'], NeverType::class => ['never', 'never-return', 'never-returns', 'no-return']];
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
