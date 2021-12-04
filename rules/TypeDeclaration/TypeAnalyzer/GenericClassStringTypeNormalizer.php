<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeAnalyzer;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
use Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer;

final class GenericClassStringTypeNormalizer
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly DetailedTypeAnalyzer $detailedTypeAnalyzer,
        private readonly UnionTypeAnalyzer $unionTypeAnalyzer
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

        if ($type instanceof UnionType && ! $this->unionTypeAnalyzer->isNullable($type, true)) {
            return $this->resolveClassStringInUnionType($type);
        }

        if ($type instanceof ArrayType && $type->getKeyType() instanceof UnionType) {
            return $this->resolveArrayTypeWithUnionKeyType($type);
        }

        return $type;
    }

    public function isAllGenericClassStringType(UnionType $unionType): bool
    {
        foreach ($unionType->getTypes() as $type) {
            if (! $type instanceof GenericClassStringType) {
                return false;
            }
        }

        return true;
    }

    private function resolveArrayTypeWithUnionKeyType(ArrayType $arrayType): ArrayType
    {
        $itemType = $arrayType->getItemType();

        if (! $itemType instanceof UnionType) {
            return $arrayType;
        }

        $keyType = $arrayType->getKeyType();
        $isAllGenericClassStringType = $this->isAllGenericClassStringType($itemType);

        if (! $isAllGenericClassStringType) {
            return new ArrayType($keyType, new MixedType());
        }

        if ($this->detailedTypeAnalyzer->isTooDetailed($itemType)) {
            return new ArrayType($keyType, new ClassStringType());
        }

        return $arrayType;
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

            if ($itemType instanceof ArrayType) {
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
        if ($classReflection->isBuiltin()) {
            return new GenericClassStringType(new ObjectType($value));
        }

        if (str_contains($value, '\\')) {
            return new GenericClassStringType(new ObjectType($value));
        }

        return new StringType();
    }
}
