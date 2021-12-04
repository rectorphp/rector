<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer
     */
    private $detailedTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer $detailedTypeAnalyzer, \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer $unionTypeAnalyzer)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->detailedTypeAnalyzer = $detailedTypeAnalyzer;
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
    }
    /**
     * @return \PHPStan\Type\ArrayType|\PHPStan\Type\Type|\PHPStan\Type\UnionType
     */
    public function normalize(\PHPStan\Type\Type $type)
    {
        $type = \PHPStan\Type\TypeTraverser::map($type, function (\PHPStan\Type\Type $type, $callback) : Type {
            if (!$type instanceof \PHPStan\Type\Constant\ConstantStringType) {
                return $callback($type);
            }
            $value = $type->getValue();
            // skip string that look like classe
            if ($value === 'error') {
                return $callback($type);
            }
            if (!$this->reflectionProvider->hasClass($value)) {
                return $callback($type);
            }
            return $this->resolveStringType($value);
        });
        if ($type instanceof \PHPStan\Type\UnionType && !$this->unionTypeAnalyzer->isNullable($type, \true)) {
            return $this->resolveClassStringInUnionType($type);
        }
        if ($type instanceof \PHPStan\Type\ArrayType && $type->getKeyType() instanceof \PHPStan\Type\UnionType) {
            return $this->resolveArrayTypeWithUnionKeyType($type);
        }
        return $type;
    }
    public function isAllGenericClassStringType(\PHPStan\Type\UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $type) {
            if (!$type instanceof \PHPStan\Type\Generic\GenericClassStringType) {
                return \false;
            }
        }
        return \true;
    }
    private function resolveArrayTypeWithUnionKeyType(\PHPStan\Type\ArrayType $arrayType) : \PHPStan\Type\ArrayType
    {
        $itemType = $arrayType->getItemType();
        if (!$itemType instanceof \PHPStan\Type\UnionType) {
            return $arrayType;
        }
        $keyType = $arrayType->getKeyType();
        $isAllGenericClassStringType = $this->isAllGenericClassStringType($itemType);
        if (!$isAllGenericClassStringType) {
            return new \PHPStan\Type\ArrayType($keyType, new \PHPStan\Type\MixedType());
        }
        if ($this->detailedTypeAnalyzer->isTooDetailed($itemType)) {
            return new \PHPStan\Type\ArrayType($keyType, new \PHPStan\Type\ClassStringType());
        }
        return $arrayType;
    }
    /**
     * @return \PHPStan\Type\ArrayType|\PHPStan\Type\UnionType
     */
    private function resolveClassStringInUnionType(\PHPStan\Type\UnionType $type)
    {
        $unionTypes = $type->getTypes();
        foreach ($unionTypes as $unionType) {
            if (!$unionType instanceof \PHPStan\Type\ArrayType) {
                return $type;
            }
            $keyType = $unionType->getKeyType();
            $itemType = $unionType->getItemType();
            if ($itemType instanceof \PHPStan\Type\ArrayType) {
                $arrayType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
                return new \PHPStan\Type\ArrayType($keyType, $arrayType);
            }
            if (!$keyType instanceof \PHPStan\Type\MixedType && !$keyType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
                return $type;
            }
            if (!$itemType instanceof \PHPStan\Type\ClassStringType) {
                return $type;
            }
        }
        return new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\ClassStringType());
    }
    /**
     * @return \PHPStan\Type\Generic\GenericClassStringType|\PHPStan\Type\StringType
     */
    private function resolveStringType(string $value)
    {
        $classReflection = $this->reflectionProvider->getClass($value);
        if ($classReflection->isBuiltin()) {
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($value));
        }
        if (\strpos($value, '\\') !== \false) {
            return new \PHPStan\Type\Generic\GenericClassStringType(new \PHPStan\Type\ObjectType($value));
        }
        return new \PHPStan\Type\StringType();
    }
}
