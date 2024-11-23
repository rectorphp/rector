<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Type;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
final class StaticTypeAnalyzer
{
    /**
     * @readonly
     */
    private UnionTypeAnalyzer $unionTypeAnalyzer;
    public function __construct(UnionTypeAnalyzer $unionTypeAnalyzer)
    {
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
    }
    public function isAlwaysTruableType(Type $type) : bool
    {
        if ($type instanceof MixedType) {
            return \false;
        }
        if ($type instanceof ConstantArrayType) {
            return \true;
        }
        if ($type instanceof ArrayType) {
            return $this->isAlwaysTruableArrayType($type);
        }
        if ($type instanceof UnionType && $this->unionTypeAnalyzer->isNullable($type)) {
            return \false;
        }
        // always trueish
        if ($type instanceof ObjectType) {
            return \true;
        }
        if ($type instanceof ConstantScalarType && !$type->isNull()->yes()) {
            return (bool) $type->getValue();
        }
        if ($type->isScalar()->yes()) {
            return \false;
        }
        return $this->isAlwaysTruableUnionType($type);
    }
    private function isAlwaysTruableUnionType(Type $type) : bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$this->isAlwaysTruableType($unionedType)) {
                return \false;
            }
        }
        return \true;
    }
    private function isAlwaysTruableArrayType(ArrayType $arrayType) : bool
    {
        $itemType = $arrayType->getIterableValueType();
        if (!$itemType instanceof ConstantScalarType) {
            return \false;
        }
        return (bool) $itemType->getValue();
    }
}
