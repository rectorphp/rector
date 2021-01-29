<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\Mapper;

use Nette\Utils\Strings;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\StaticTypeMapper\ValueObject\Type\FalseBooleanType;

final class ScalarStringToTypeMapper
{
    /**
     * @var array<class-string<Type>, string[]>
     */
    private const SCALAR_NAME_BY_TYPE = [
        StringType::class => ['string'],
        FloatType::class => ['float', 'real', 'double'],
        IntegerType::class => ['int', 'integer'],
        FalseBooleanType::class => ['false'],
        BooleanType::class => ['true', 'bool', 'boolean'],
        NullType::class => ['null'],
        VoidType::class => ['void'],
        ResourceType::class => ['resource'],
        CallableType::class => ['callback', 'callable'],
        ObjectWithoutClassType::class => ['object'],
    ];

    public function mapScalarStringToType(string $scalarName): Type
    {
        $loweredScalarName = Strings::lower($scalarName);

        foreach (self::SCALAR_NAME_BY_TYPE as $objectType => $scalarNames) {
            if (! in_array($loweredScalarName, $scalarNames, true)) {
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
            return new MixedType(true);
        }

        return new MixedType();
    }
}
