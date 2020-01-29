<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\TypeMapper;

use Nette\Utils\Strings;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;

final class ScalarStringToTypeMapper
{
    public function mapScalarStringToType(string $scalarName): Type
    {
        $loweredScalarName = Strings::lower($scalarName);

        $scalarNameByType = [
            StringType::class => ['string'],
            FloatType::class => ['float', 'real', 'double'],
            IntegerType::class => ['int', 'integer'],
            BooleanType::class => ['false', 'true', 'bool', 'boolean'],
            NullType::class => ['null'],
            VoidType::class => ['void'],
            ResourceType::class => ['resource'],
            CallableType::class => ['callback', 'callable'],
            ObjectWithoutClassType::class => ['object'],
        ];

        foreach ($scalarNameByType as $objectType => $scalarNames) {
            if (! in_array($loweredScalarName, $scalarNames, true)) {
                continue;
            }

            return new $objectType();
        }

        if ($loweredScalarName === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if ($loweredScalarName === 'mixed') {
            return new MixedType(true);
        }

        return new MixedType();
    }
}
