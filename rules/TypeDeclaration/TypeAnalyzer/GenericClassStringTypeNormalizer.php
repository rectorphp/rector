<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;

final class GenericClassStringTypeNormalizer
{
    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function normalize(Type $type): ArrayType | UnionType | Type
    {
        $type = TypeTraverser::map($type, function (Type $type, $callback): Type {
            if (! $type instanceof ConstantStringType) {
                return $callback($type);
            }

            $value = $type->getValue();

            // skip string that look like classe
            if ($value === 'error') {
                return $callback($type);
            }

            if (! $this->reflectionProvider->hasClass($value)) {
                return $callback($type);
            }

            return $this->resolveStringType($value);
        });

        if ($type instanceof UnionType) {
            return $this->resolveClassStringInUnionType($type);
        }

        return $type;
    }

    private function resolveClassStringInUnionType(UnionType $type): UnionType | ArrayType
    {
        $unionTypes = $type->getTypes();

        foreach ($unionTypes as $unionType) {
            if (! $unionType instanceof ArrayType) {
                return $type;
            }

            $keyType = $unionType->getKeyType();
            $itemType = $unionType->getItemType();

            if ($itemType instanceof ConstantArrayType) {
                $arrayType = new ArrayType(new MixedType(), new MixedType());
                return new ArrayType($keyType, $arrayType);
            }

            if (! $keyType instanceof MixedType && ! $keyType instanceof ConstantIntegerType) {
                return $type;
            }

            if (! $itemType instanceof ClassStringType) {
                return $type;
            }
        }

        return new ArrayType(new MixedType(), new ClassStringType());
    }

    private function resolveStringType(string $value): GenericClassStringType | StringType
    {
        $classReflection = $this->reflectionProvider->getClass($value);
        if ($classReflection->isBuiltIn()) {
            return new GenericClassStringType(new ObjectType($value));
        }
        if (str_contains($value, '\\')) {
            return new GenericClassStringType(new ObjectType($value));
        }
        return new StringType();
    }
}
